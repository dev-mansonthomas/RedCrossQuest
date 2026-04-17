# RedCrossQuest — environnement Docker

Documentation technique de la dockerisation du projet. Pour la procédure de
démarrage quotidienne, voir [`run_local.md`](./run_local.md).

## Objectifs

- Reproduire fidèlement le runtime **Google App Engine `php83`** (Nginx +
  PHP-FPM 8.3 + extensions identiques).
- Supprimer toute dépendance à un toolchain local (brew, nvm, sphp, PECL,
  Apache/httpd, MariaDB/MySQL, Composer global…).
- Rendre le stack **portable** (macOS Intel/ARM, Linux, CI) et modulaire
  (passage futur à PHP 8.5 via un simple `--build-arg PHP_VERSION=8.5`).

## Architecture

```
┌──────────────────┐      ┌──────────────────┐      ┌──────────────────┐
│  node-client     │      │  nginx (8080)    │◄─────┤  php-fpm (9000)  │
│  gulp serve 3000 │─────►│  mime GAE serve  │      │  PHP 8.3 + exts  │
│  Node 10.24.1    │ /rest│  basePath /rest  │      │  grpc, protobuf, │
│                  │      │                  │      │  xdebug, sodium  │
└──────────────────┘      └──────────────────┘      └────────┬─────────┘
         │                                                    │
         └────────────── network rcq_net ────────────────────┤
                                                              ▼
                                                     ┌──────────────────┐
                                                     │  mariadb (3306)  │
                                                     │  schema: rcq     │
                                                     └──────────────────┘
```

| Service       | Image                          | Port host         | Rôle                                      |
|---------------|--------------------------------|-------------------|-------------------------------------------|
| `php-fpm`     | `php:8.3-fpm-bookworm` (custom)| —                 | Runtime PHP-FPM, cible `dev` = + xdebug   |
| `nginx`       | `nginx:1.27-alpine` (custom)   | `8080`            | Reproduction du wrapper GAE `serve`       |
| `mariadb`     | `mariadb:11.4`                 | `3306`            | MySQL local (dev uniquement)              |
| `node-client` | `node:10-buster` (custom)      | `3000`, `3001`    | Build & `gulp serve` du front AngularJS   |

## Mapping avec GAE

| Côté GAE (prod)                      | Côté Docker (dev)                                     |
|--------------------------------------|-------------------------------------------------------|
| `runtime: php83` (Nginx + PHP-FPM)   | `nginx` + `php-fpm` containers                        |
| `entrypoint: serve public/rest/…`    | `nginx` vhost pointe sur `/app/server/public/rest/`   |
| Cloud SQL Unix socket                | TCP vers `mariadb:3306` (DSN dans `.env`)             |
| Secret Manager                       | `.cred/` monté read-only + `GOOGLE_APPLICATION_CREDENTIALS` |
| Extensions natives bundled           | `pecl install grpc protobuf` + `docker-php-ext-install` |

## Modularité / évolutivité

Tous les composants versionnés sont paramétrables via `.env` :

```dotenv
PHP_VERSION=8.3         # bump to 8.5 when GAE exposes php85
NODE_VERSION=10.24.1    # bump when the front is migrated off AngularJS 1.x
MARIADB_VERSION=11.4
```

Après modification : `make rebuild && make up`.

## Fichiers clés

```
.
├── docker-compose.yml               Orchestration (4 services, 1 network)
├── .env.example                     Template de config — copié en .env
├── run_local.sh                     Bootstrap one-shot
├── Makefile                         Cibles développeur (`make help`)
├── .dockerignore                    Exclusions du build context
└── docker/
    ├── php/
    │   ├── Dockerfile               ARG PHP_VERSION, cibles `base` et `dev`
    │   ├── php.ini                  timezone Europe/Paris, opcache
    │   ├── www.conf                 PHP-FPM pool
    │   └── xdebug.ini               XDEBUG_MODE piloté par .env
    ├── nginx/
    │   ├── Dockerfile
    │   ├── nginx.conf
    │   └── rcq-backend.conf         basePath /rest, FastCGI -> php-fpm:9000
    ├── node/
    │   ├── Dockerfile               Node 10 + gulp/bower + socat
    │   └── entrypoint.sh            forward localhost:8080 -> nginx:8080
    ├── mariadb/
    │   ├── my.cnf                   sql_mode compatible Spotfire
    │   └── initdb/01-create-schema.sql
    └── config/
        ├── settings.docker.php      Copié en server/src/settings.php
        └── phinx.docker.yml         Copié en server/phinx.yml
```

## Intégration avec le workflow GCP

Les scripts `GCP/deploy_back.sh` et `GCP/deploy_front.sh` ont été adaptés :

- **Build front** : exécuté dans le conteneur `node-client`
  (`docker compose run --rm --no-deps --entrypoint "" node-client bash -lc …`).
  Plus besoin de Node 10 sur le host.
- **Migration Phinx** : exécutée dans `php-fpm` ;
  `sed` réécrit `host: 127.0.0.1` → `host: host.docker.internal`
  pour que le conteneur atteigne le `cloud-sql-proxy` lancé sur le host
  (ports 3305/3307/3308/3310 selon l'env).
- **`cloud-sql-proxy`** reste piloté depuis le host (pas de conteneur dédié)
  car c'est `gcp-deploy.sh` qui l'orchestre déjà et gère le kill -15.

`deploy_cloudFunctions.sh` n'est pas modifié : il opère sur un autre repo
(`~/RedCrossQuestCloudFunctions/`).

## Volumes persistants

| Volume              | Contenu                                       |
|---------------------|-----------------------------------------------|
| `mariadb-data`      | Données MySQL (conservées entre `make down`)  |
| `composer-cache`    | Cache `/tmp/composer`                         |
| `php-di-cache`      | Cache PHP-DI compilé (`/tmp/php-di-compiled`) |
| `node-modules`      | `client/node_modules` (isolé du bind mount)   |
| `bower-components`  | `client/bower_components`                     |

`make clean` supprime les containers et volumes (⚠️ détruit la DB locale).

## Xdebug / IntelliJ

Dans `.env`, mettre `XDEBUG_MODE=debug`. IntelliJ :

- *PHP → Servers* : host `localhost`, port `8080`, path mapping
  `./server` → `/app/server`.
- *PHP → Debug* : port `9003`.
- Xdebug Helper (Chrome) : activer sur `localhost:3000`.

Le conteneur résout `host.docker.internal` → host gateway (configuré dans
`docker-compose.yml` via `extra_hosts`).

## Sécurité

- `.env` et `.cred/` sont dans `.gitignore` ET `.dockerignore`.
- Aucun secret n'est baké dans les images.
- `server/phinx.yml` est également git-ignoré (contient `pass:`).
