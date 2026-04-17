# RedCrossQuest — démarrage de l'environnement local

Guide opérationnel pour lancer le projet sur votre poste. Pour la vue
architecture, voir [`docker.md`](./docker.md).

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ≥ 4.30
  (Docker Engine ≥ 26, Compose v2).
- 8 Go de RAM libre recommandés (4 Go minimum).
- macOS (Intel ou Apple Silicon), Linux ou WSL2.

**Aucune autre dépendance** n'est requise (ni Node, ni PHP, ni MariaDB,
ni brew, ni nvm, ni sphp).

## Démarrage en une commande

```bash
./run_local.sh
# ou, équivalent :
make init
```

Ce que fait le script :

1. Crée `.env` à partir de `.env.example` (si absent).
2. Crée le dossier `.cred/` (monté en read-only dans les conteneurs).
3. Sème `server/src/settings.php` depuis `docker/config/settings.docker.php`
   **uniquement si le fichier n'existe pas** — jamais d'écrasement.
4. Sème `server/phinx.yml` depuis `docker/config/phinx.docker.yml` (avec
   substitution du mot de passe root défini dans `.env`).
5. Build les images Docker (`php-fpm`, `nginx`, `node-client`).
6. Démarre `mariadb`, attend le healthcheck (jusqu'à 60 s).
7. Lance `composer install`, `npm install`, `bower install`.
8. Exécute `phinx migrate -e docker`.
9. Affiche les URLs utilisables.

### Options

| Option          | Effet                                                    |
|-----------------|----------------------------------------------------------|
| `--skip-deps`   | Saute `composer install` / `npm install` / `bower install` |
| `--rebuild`     | Force `docker compose build --no-cache --pull`            |
| `-h`, `--help`  | Affiche l'aide                                            |

## URLs exposées

| Service               | URL                                            |
|-----------------------|------------------------------------------------|
| Backend REST          | http://localhost:8080/rest                     |
| Frontend (gulp serve) | http://localhost:3000                          |
| Browser-Sync UI       | http://localhost:3001                          |
| MariaDB (DBeaver)     | `localhost:3306` (user `root`, pw `$MYSQL_ROOT_PASSWORD`) |
| PHP healthcheck       | http://localhost:8080/healthz                  |

## Cycle de développement au quotidien

```bash
make up              # démarrer (sans rebuild)
make down            # stopper (conserve les volumes)
make restart         # down + up
make logs            # tail de tous les services
make ps              # services en cours
```

### Dépendances

```bash
make composer cmd="require foo/bar"
make composer-install                # re-run composer install
make npm cmd="install --save-dev lodash"
make bower cmd="install angular-ui-router"
```

### Base de données / migrations

```bash
make phinx cmd="status"              # état des migrations
make phinx-migrate                   # appliquer les migrations en attente
make phinx-rollback                  # rollback la dernière
make db-cli                          # client mariadb interactif
```

### Front-end

```bash
make gulp-serve                      # gulp serve attaché (Ctrl-C pour quitter)
make gulp-build                      # build de production (dist/)
```

### Shells

```bash
make shell-php                       # bash dans le conteneur php-fpm
make shell-node                      # bash dans node-client
make shell-db                        # bash dans mariadb
```

### Diagnostic

```bash
make doctor                          # versions de chaque composant
docker compose config --quiet        # valide la syntaxe du compose
```

## Gestion des secrets

- **`.env`** : paramètres non sensibles (URLs, flags, ports). Copié depuis
  `.env.example`, git-ignoré.
- **`.cred/`** : fichiers de credentials (clé GCP service account, certificats).
  Monté en read-only dans `php-fpm` sous `/run/secrets/`. Git-ignoré.
- **Google Application Credentials** : placer le JSON dans `.cred/gcp-sa.json`
  puis pointer `GOOGLE_APPLICATION_CREDENTIALS=/run/secrets/gcp-sa.json`
  dans `.env`.

## Reset complet

```bash
make clean                           # supprime les conteneurs + volumes
make nuke                            # + supprime les images locales
```

⚠️ `make clean` détruit la base de données locale. Relancer `./run_local.sh`
pour repartir d'un état propre.

## Déploiement GCP

Le `run_local.sh` n'est **pas** utilisé pour déployer. Continuez à utiliser :

```bash
./gcp-deploy.sh fr dev all
```

Les sous-scripts (`GCP/deploy_back.sh`, `GCP/deploy_front.sh`) utilisent
désormais les images Docker du repo pour builder et migrer. Vous n'avez
plus besoin de Node 10 / PHP 8.3 / Composer installés sur le host.

`cloud-sql-proxy` reste lancé côté host (ports 3305/3307/3308/3310), comme
avant — c'est `gcp-deploy.sh` qui l'orchestre.

## Problèmes fréquents

| Symptôme                                    | Cause probable / remède                                  |
|---------------------------------------------|----------------------------------------------------------|
| `MariaDB did not become healthy in time`    | Docker Desktop pas démarré, ou port 3306 occupé          |
| `bind: address already in use`              | Changer `HOST_PORT_*` dans `.env`                        |
| `npm install` échoue sur Apple Silicon      | Normal au 1er run ; l'image `linux/amd64` est émulée     |
| Xdebug ne répond pas                        | `.env` → `XDEBUG_MODE=debug`, puis `make restart`        |
| Phinx ne voit pas les migrations            | `make phinx cmd="status -c /app/server/phinx.yml -e docker"` |
| `gulp serve` ne détecte pas les changements | Normal sur macOS ; `CHOKIDAR_USEPOLLING` est déjà activé |

## Aller plus loin

Voir [`docker.md`](./docker.md) pour les choix d'architecture, le mapping
GAE ↔ Docker, la modularité (bump PHP 8.5), les volumes et l'intégration
Xdebug.
