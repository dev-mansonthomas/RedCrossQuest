# RedCrossQuest — démarrage de l'environnement local

Guide opérationnel pour lancer le projet sur votre poste. Pour la vue
architecture, voir [`docker.md`](./docker.md).

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ≥ 4.30
  (Docker Engine ≥ 26, Compose v2).
- 8 Go de RAM libre recommandés (4 Go minimum).
- macOS (Intel ou Apple Silicon), Linux ou WSL2.
- Le conteneur **`rcq_mysql`** (fourni par le projet _dashboard_) démarré
  et exposant le port hôte `3316`.
- Entrée `/etc/hosts` sur l'hôte : `127.0.0.1 rcq`.
- Fichier de service account GCP dans `~/.cred/` (par défaut :
  `~/.cred/rcq-fr-dev-61e86fa56dc1.json`).

**Aucune autre dépendance** n'est requise (ni Node, ni PHP, ni MySQL client,
ni brew, ni nvm, ni sphp).

## Démarrage en une commande

```bash
./run_local.sh
# ou, équivalent :
make init
```

Ce que fait le script :

1. Vérifie Docker (CLI, Compose v2, daemon).
2. Crée `.env` à partir de `.env.example` (si absent).
3. Vérifie la présence du JSON de service account dans `~/.cred/`.
4. Sème `server/src/settings.php` depuis `server/src/settings.sample.php`
   **uniquement si le fichier n'existe pas** — jamais d'écrasement.
5. Vérifie que le conteneur `rcq_mysql` est démarré ; le redémarre s'il
   existe mais est arrêté ; abandonne proprement sinon.
6. Build les images Docker (`php-fpm`, `nginx`, `node-client`).
7. Démarre `php-fpm`, `nginx`, `node-client`.
8. Lance `composer install`, `npm install`, `bower install`.
9. Affiche les URLs utilisables.

Les migrations **Phinx** ne sont **pas** lancées automatiquement : exécutez-les
manuellement quand vous le souhaitez (`make phinx cmd=migrate` ou votre
workflow habituel).

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
| MySQL (DBeaver)       | `rcq:3316` (via `/etc/hosts`) — schéma `rcq_fr_dev_db` |
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

La base MySQL est externe (conteneur `rcq_mysql`, projet dashboard). Ce
projet n'en gère **que le schéma** via Phinx — à exécuter manuellement :

```bash
make phinx cmd="status"              # état des migrations
make phinx cmd="migrate"             # appliquer les migrations en attente
make phinx cmd="rollback"            # rollback la dernière
# Environnement non-défaut : `make phinx cmd=status env=local`
```

Pour un client interactif, utilisez DBeaver / `mysql` CLI depuis l'hôte en
pointant sur `rcq:3316` (ou `127.0.0.1:3316`).

### Front-end

```bash
make gulp-serve                      # gulp serve attaché (Ctrl-C pour quitter)
make gulp-build                      # build de production (dist/)
```

### Shells

```bash
make shell-php                       # bash dans le conteneur php-fpm
make shell-node                      # bash dans node-client
```

### Diagnostic

```bash
make doctor                          # versions + état du conteneur rcq_mysql
docker compose config --quiet        # valide la syntaxe du compose
```

## Gestion des secrets

- **`.env`** : paramètres non sensibles (URLs, flags, ports, DSN sans mot
  de passe). Copié depuis `.env.example`, git-ignoré.
- **`~/.cred/`** (sur l'hôte) : JSON de service account GCP. Monté en
  read-only dans `php-fpm` sous `/run/secrets/`. Jamais versionné.
- **`GOOGLE_APPLICATION_CREDENTIALS`** : pointe vers le chemin **à l'intérieur
  du conteneur** (`/run/secrets/rcq-fr-dev-…json` par défaut).
- **Mots de passe** : récupérés à l'exécution par `SecretManagerService`
  depuis GCP Secret Manager. En mode dev (`APP_URL` contient `localhost`
  ou `rcq`), le service préfixe automatiquement les clés par `local-`
  (ex. `local-MYSQL_PASSWORD`). Aucun mot de passe en clair sur disque.

## Reset complet

```bash
make clean                           # supprime les conteneurs RCQ + volumes
make nuke                            # + supprime les images locales
```

La base MySQL externe (`rcq_mysql`) n'est **pas** affectée par `make clean` :
elle appartient au projet dashboard.

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
| `Container 'rcq_mysql' not found`           | Démarrer la stack MySQL depuis le projet dashboard       |
| `GCP service-account JSON not found`        | Déposer le fichier dans `~/.cred/` (cf. `.env.example`)  |
| Erreur Secret Manager au démarrage          | Vérifier `GOOGLE_CLOUD_PROJECT=rcq-fr-dev` et les droits du SA |
| `bind: address already in use`              | Changer `HOST_PORT_*` dans `.env`                        |
| `npm install` échoue sur Apple Silicon      | Normal au 1er run ; l'image `linux/amd64` est émulée     |
| Xdebug ne répond pas                        | `.env` → `XDEBUG_MODE=debug`, puis `make restart`        |
| Phinx ne voit pas les migrations            | `make phinx cmd=status env=<votre-env>`                  |
| `gulp serve` ne détecte pas les changements | Normal sur macOS ; `CHOKIDAR_USEPOLLING` est déjà activé |

## Aller plus loin

Voir [`docker.md`](./docker.md) pour les choix d'architecture, le mapping
GAE ↔ Docker, la modularité (bump PHP 8.5), les volumes et l'intégration
Xdebug.
