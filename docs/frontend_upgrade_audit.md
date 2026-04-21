# Audit frontend `client/` — état, vulnérabilités et plan d'upgrade

> Branche : `technical_upgrade`
> Date : 2026-04-21.
> Contrainte produit : **rester sur AngularJS** (pas de migration Angular 2+). Objectif : monter AngularJS de 1.7.x → 1.8.3 (dernière release), stabiliser la toolchain, éliminer les vulnérabilités critiques.

---

## 1. État actuel

### 1.1 Runtime (exécuté par l'utilisateur final)

| Composant | Version utilisée | Source | Dernière version stable |
|---|---|---|---|
| AngularJS | `~1.7.8` *(résolution Bower)* | `client/bower.json` `resolutions.angular` | **1.8.3** (dernière release, support LTS terminé chez Google, maintenance communautaire) |
| `angular-*` (animate, cookies, touch, sanitize, messages, aria, resource, route, i18n) | `~1.8.2` *(demandé)* mais alignés sur **1.7.8** par `resolutions` | `client/bower.json` | 1.8.3 |
| `angular-bootstrap` (ui.bootstrap) | `^2.5.0` | `client/bower.json` | 2.5.6 |
| `bootstrap-sass` | `3.4.1` | `client/bower.json` | 3.4.3 |
| `jquery` | `~2.2.4` | `client/bower.json` | 3.7.1 (jQuery 2 fin de support) |
| `moment` | `~2.29.1` | `client/bower.json` | 2.30.1 (projet en maintenance) |
| `angular-jwt` | `^0.1.11` | `client/bower.json` | 0.1.11 (abandonné, passer à `auth0-angular-jwt` si besoin) |
| `angular-toastr` | `~2.1.1` | `client/bower.json` | 3.1.1 |
| `ngstorage`, `angular-local-storage` | `^0.3.11` / `^0.7.1` | | stables, peu maintenus |

**Entrypoint module** `client/src/app/index.module.js` — déclare `redCrossQuestClient` avec les modules standards 1.7/1.8 + `qrScannerU`, `ngAudio`, `ja.qr`, `ngMap`, `ngStorage`, `angular-jwt`.

### 1.2 Build & développement (ne part pas en prod)

| Composant | Version | Notes |
|---|---|---|
| Node | **10.24.1** (pinné dans `client/package.json` `engines` et `docker/node/Dockerfile`) | Node 10 EOL depuis 2021-04 ; image `node:10-buster` encore pullable, Buster Debian archivé. |
| Gulp | **3.9.1** | Gulp 3 EOL depuis avril 2022. Incompatible Node 12+. |
| gulp-sass | `~3.2.1` | Dépend de `node-sass` (libsass) lui-même EOL depuis 2022-10. |
| Bower | 1.8.14 (installé globalement) | **Déprécié officiellement** par son équipe depuis 2017. |
| phantomjs | `~2.1.7` | Archivé depuis 2018 ; Karma tourne encore grâce à `karma-phantomjs-launcher`. |
| Protractor | via `gulp-protractor@^4.1.1` | **Abandonné** par Angular en 2022. |
| Karma | `~6.3.16` | Karma 6 encore maintenu (v6 dernière branche). |

### 1.3 Déploiement GAE

- `client/app.yaml` : `runtime: python312` (✅ à jour depuis PR technical_upgrade précédente), service `front`, static serving du dossier `dist/`. **Aucune action nécessaire** côté GAE runtime.
- Pipeline : `GCP/deploy_front.sh` construit via gulp puis publie `dist/` en static files.

---

## 2. Vulnérabilités `npm audit` (`client/`)

Résultat `npm audit` (2026-04-21, Node 10 / npm 6 via le container) :

| Sévérité | Count |
|---|---:|
| **critical** | 9 |
| **high** | 83 |
| moderate | 35 |
| low | 11 |
| **Total** | **138** |

### 2.1 Critiques (9)

Exemples : `extract-zip`, `form-data`, `gulp-ng-annotate`, `lodash`, `minimist`, `mkdirp`.

Toutes sont **dans la chaîne de build (`devDependencies`)**, pas dans le bundle livré au navigateur. Donc :

- Pas d'exposition directe en prod (ni XSS ni RCE côté utilisateur).
- **Risque supply-chain** : un contributeur qui lance `npm install` peut subir une exploitation (`extract-zip` path traversal, `minimist` prototype pollution, etc.).

### 2.2 Élevées (83)

Dominées par le sous-arbre `gulp-3` + `@npmcli/*` (tiré par `npm@^8.19.3` listé en dep `dependencies` – voir §4.1).

### 2.3 Analyse par axe

- **dependencies runtime** (listées dans `package.json` > `dependencies`) : `bl`, `latest-version`, `natives`, `npm`. **Aucune n'est importée par le bundle AngularJS livré** — elles sont là uniquement pour des outils de build (`natives` pour Node 10, `npm` pour des scripts).
- **devDependencies** : porte la quasi-totalité du poids. Remédiation = migrer la toolchain.

---

## 3. Recommandations

### 3.1 Prioritaires (stabilité + sécurité)

1. **Bump Bower resolutions vers AngularJS 1.8.3** (effort : faible, risque : faible).
   - `client/bower.json` `resolutions.angular` : `~1.7.8` → `~1.8.3`.
   - Toutes les `angular-*` sont déjà demandées en `~1.8.2` ; la résolution force aujourd'hui 1.7.8 — incohérence à résoudre.
   - Relancer `bower install` dans le container `node-client`, tester tous les écrans.
2. **Nettoyer `dependencies` de `package.json`** : retirer `bl`, `latest-version`, `natives`, `npm` (elles n'ont aucune raison d'être `dependencies` runtime d'un front statique) → réduit la surface audit sans casser le build.
3. **Figer la toolchain dans Docker** — déjà fait dans `docker/node/Dockerfile` (Node 10.24.1 + Gulp 3.9.1 + Bower 1.8.14). Vérifier que `run_local.md` et `docker.md` documentent bien que **personne ne doit `npm install` hors container**.

### 3.2 Moyen terme (dette technique)

4. **Migrer Gulp 3 → Gulp 4** (effort : moyen). Gulp 4 fonctionne sur Node 12+. Nécessite de réécrire les fichiers `client/gulp/*.js` pour passer de `gulp.task(name, [deps], fn)` à `gulp.series`/`gulp.parallel`. Débloque la sortie de Node 10.
5. **Remplacer `gulp-sass@3` + `node-sass`** par `gulp-sass@5` + `sass` (dart-sass). Tester que `bootstrap-sass@3.4.1` compile toujours (peut nécessiter des ajustements de `@import`).
6. **Remplacer Bower par `npm` + `main-bower-files`** ou migrer les assets vers un bundler moderne. Bower est déprécié depuis 2017, chaque nouvelle dépendance frontend devient un casse-tête.
7. **Retirer PhantomJS** au profit de `karma-chrome-launcher` en mode headless. PhantomJS est archivé, Chrome headless est plus stable et plus rapide.
8. **Retirer Protractor** (tests E2E) au profit de Playwright ou Cypress. Protractor est officiellement déprécié par l'équipe Angular.

### 3.3 Points de vigilance

- **Compatibilité navigateur** : AngularJS 1.8 supporte IE11+, Edge Chromium, Chrome/Firefox/Safari récents. Aucun breaking côté navigateur entre 1.7.8 et 1.8.3.
- **`angular-jwt@0.1.x`** est très ancien mais la surface utilisée par RCQ est minime (intercepteur + helper decode). Peut rester en l'état ou être réécrit en ~20 lignes maison si besoin.
- **`jquery@2.2.4`** : utilisé par `bootstrap 3` + `ngMap`. Upgrader à jQuery 3 casse `bootstrap 3` (nécessite `bootstrap-migrate` et tests UI exhaustifs). **Ne pas toucher** sauf si on migre Bootstrap 3 → 5 (hors scope).
- **Breaking 1.7 → 1.8** : principalement `$location.hashPrefix('')` par défaut (déjà géré par RCQ) + `ngPattern` qui compile les strings en regex *sans flags*. À re-tester sur les formulaires (login, register UL, save tronc).

---

## 4. Plan d'action priorisé

| # | Action | Impact | Effort | PR cible |
|---|---|---|---|---|
| F1 | `bower.json` : `resolutions.angular` → `~1.8.3` + `bower install` | runtime ✅ | 1h | `feat(client): bump AngularJS 1.7 → 1.8` |
| F2 | Nettoyer `package.json.dependencies` (retirer `bl`, `npm`, `latest-version`, `natives`) | audit -20% | 30 min | `chore(client): prune unused runtime deps` |
| F3 | Documenter dans `run_local.md` que `npm install` doit être fait **uniquement** via `docker compose run --rm node-client npm install` | sécurité supply-chain | 15 min | même commit que F2 |
| F4 | Smoke tests E2E manuels (Login, Export, TroncQueteur prepare+save, UL registration) après F1 | validation | 1-2h | post-F1 |
| F5 | *(long terme)* Gulp 3 → 4, Node 10 → 20, Bower → npm/vite | debt | 3-5 j | hors scope `technical_upgrade` — à planifier en sprint dédié |

### 4.1 Cible fin `technical_upgrade`

Limiter la branche `technical_upgrade` aux étapes **F1 + F2 + F3**. La dette toolchain (F5) dépasse le cadre d'une « montée technique stable ».

---

## 5. Références

- AngularJS 1.8 release notes : https://github.com/angular/angular.js/blob/master/CHANGELOG.md
- Gulp 3 → 4 migration guide : https://gulpjs.com/docs/en/getting-started/migration-guide
- npm advisories liés à `gulp-ng-annotate`, `minimist`, `lodash` : voir `npm audit` (JSON complet régénérable via `docker compose run --rm node-client npm audit --json`).
- Commit précédent de bump GAE frontend vers `python312` : `client/app.yaml`.
