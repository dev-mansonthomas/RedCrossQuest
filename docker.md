# RedCrossQuest — environnement Docker

Documentation technique de la dockerisation du projet. Pour la procédure de
démarrage quotidienne, voir [`run_local.md`](./run_local.md).

## Objectifs

- Reproduire fidèlement le runtime **Google App Engine `php83`** (Nginx +
  PHP-FPM 8.3 + extensions identiques).
- Supprimer toute dépendance à un toolchain local (brew, nvm, sphp, PECL,
  Apache/httpd, Composer global…).
- Rendre le stack **portable** (macOS Intel/ARM, Linux, CI) et modulaire
  (passage futur à PHP 8.5 via un simple `--build-arg PHP_VERSION=8.5`).

> **La base MySQL n'appartient pas à ce stack.** Elle tourne dans le
> conteneur `rcq_mysql` (port hôte `3316`) fourni par le projet _dashboard_.
> `run_local.sh` se contente de vérifier qu'il est démarré.

## Architecture

```
┌──────────────────┐      ┌──────────────────┐      ┌──────────────────┐
│  node-client     │      │  nginx (8080)    │◄─────┤  php-fpm (9000)  │
│  gulp serve 3000 │─────►│  mime GAE serve  │      │  PHP 8.3 + exts  │
│  Node 10.24.1    │ /rest│  basePath /rest  │      │  grpc, protobuf, │
│                  │      │                  │      │  xdebug, sodium  │
└──────────────────┘      └──────────────────┘      └────────┬─────────┘
         │                                                    │ rcq:3316
         └────────────── network rcq_net ────────────────────┤  (host-gateway)
                                                              ▼
                                                     ┌──────────────────┐
                                                     │ rcq_mysql (3316) │
                                                     │  (EXTERNE: projet│
                                                     │   dashboard)     │
                                                     └──────────────────┘
```

| Service       | Image                          | Port host         | Rôle                                      |
|---------------|--------------------------------|-------------------|-------------------------------------------|
| `php-fpm`     | `php:8.3-fpm-bookworm` (custom)| —                 | Runtime PHP-FPM, cible `dev` = + xdebug   |
| `nginx`       | `nginx:1.27-alpine` (custom)   | `8080`            | Reproduction du wrapper GAE `serve`       |
| `node-client` | `node:10-buster` (custom)      | `3000`, `3001`    | Build & `gulp serve` du front AngularJS   |
| `rcq_mysql`   | _externe_ (projet dashboard)   | `3316`            | MySQL partagé entre les projets RCQ       |

## Mapping avec GAE

| Côté GAE (prod)                      | Côté Docker (dev)                                     |
|--------------------------------------|-------------------------------------------------------|
| `runtime: php83` (Nginx + PHP-FPM)   | `nginx` + `php-fpm` containers                        |
| `entrypoint: serve public/rest/…`    | `nginx` vhost pointe sur `/app/server/public/rest/`   |
| Cloud SQL Unix socket                | TCP vers `rcq:3316` (host-gateway → conteneur `rcq_mysql`) |
| Secret Manager (prod)                | Secret Manager (dev) via `GOOGLE_APPLICATION_CREDENTIALS` ; secrets préfixés `local-` (cf. `SecretManagerService`) |
| `env_variables:` dans `app.yaml`     | `environment:` dans `docker-compose.yml` (alimenté par `.env`) |
| Extensions natives bundled           | `pecl install grpc protobuf` + `docker-php-ext-install` |

## Modularité / évolutivité

Tous les composants versionnés sont paramétrables via `.env` :

```dotenv
PHP_VERSION=8.3         # bump to 8.5 when GAE exposes php85
NODE_VERSION=10.24.1    # bump when the front is migrated off AngularJS 1.x
```

Après modification : `make rebuild && make up`.

## Fichiers clés

```
.
├── docker-compose.yml               Orchestration (3 services, 1 network)
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
    └── node/
        ├── Dockerfile               Node 10 + gulp/bower + socat
        └── entrypoint.sh            forward localhost:8080 -> nginx:8080
```

`server/src/settings.php` est semé depuis `server/src/settings.sample.php`
au premier lancement (jamais écrasé). Ce fichier utilise `getenv()` pour
toute sa configuration, comme sur GAE — les mots de passe sont récupérés
à l'exécution via **Google Secret Manager** (préfixe `local-` détecté
automatiquement par `SecretManagerService` quand l'`appUrl` contient
`localhost` ou `rcq`).

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
| `composer-cache`    | Cache `/tmp/composer`                         |
| `php-di-cache`      | Cache PHP-DI compilé (`/tmp/php-di-compiled`) |
| `node-modules`      | `client/node_modules` (isolé du bind mount)   |
| `bower-components`  | `client/bower_components`                     |

`make clean` supprime les containers et volumes. **La base MySQL n'est
pas affectée** (elle vit dans le conteneur externe `rcq_mysql`).

## Connexion à la base

| Élément                 | Valeur                                           |
|-------------------------|--------------------------------------------------|
| Conteneur               | `rcq_mysql` (projet dashboard, non géré ici)     |
| Port hôte               | `3316`                                           |
| Hostname applicatif     | `rcq` (entrée `/etc/hosts` côté hôte, `extra_hosts` côté conteneur) |
| Schéma local            | `rcq_fr_dev_db`                                  |
| Utilisateur applicatif  | `rcq-fr-dev-user`                                |
| Mot de passe            | Secret Manager, clé `local-MYSQL_PASSWORD` (projet `rcq-fr-dev`) |

Le DSN `mysql:host=rcq;port=3316;dbname=rcq_fr_dev_db;charset=utf8mb4`
fonctionne à l'identique depuis l'hôte (DBeaver, `mysql` CLI) et depuis
le conteneur `php-fpm` grâce à l'alias `rcq:host-gateway`.

## Xdebug / IntelliJ

Dans `.env`, mettre `XDEBUG_MODE=debug`. IntelliJ :

- *PHP → Servers* : host `localhost`, port `8080`, path mapping
  `./server` → `/app/server`.
- *PHP → Debug* : port `9003`.
- Xdebug Helper (Chrome) : activer sur `localhost:3000`.

Le conteneur résout `host.docker.internal` → host gateway (configuré dans
`docker-compose.yml` via `extra_hosts`).

## Sécurité

- `.env`, `server/src/settings.php` et `server/phinx.yml` sont dans
  `.gitignore` ET `.dockerignore`.
- Aucun secret n'est baké dans les images.
- Les credentials GCP vivent dans `~/.cred/` sur l'hôte, monté read-only
  sur `/run/secrets` dans `php-fpm`.
- Les mots de passe sont récupérés à l'exécution depuis GCP Secret Manager
  — jamais écrits sur disque ni committés.
