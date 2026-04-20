# Plan de migration `technical_upgrade`

> Branche : `technical_upgrade`
> Source : `server/composer.json` + `server/composer.lock` + runtime Docker (PHP 8.3, GAE Standard `php83` → cible `php85`).
> Date d'analyse : 2026-04-20.

---

## 1. Tableau de migration des dépendances

Légende **Impact** : 🟢 mineur/patch · 🟡 mineur avec 1-2 deprecations · 🟠 majeur (API breaks) · 🔴 bloquant à auditer.

### 1.1 Cœur framework / langage

| Package | Contrainte `composer.json` | Installée (`lock`) | Dernière stable | Δ majeur ? | Impact | Notes PHP 8.5 |
|---|---|---|---|---|---|---|
| **PHP runtime** | `^8.3` | 8.3.x | **8.5.0** (20 nov 2025) | ✅ | 🟡 | Voir §2 |
| `slim/slim` | `^4.14.0` | 4.14.0 | **4.15.1** (21 nov 2025) | ❌ mineur | 🟡 | `setArgument()`/`setArguments()` **deprecated** → utiliser middleware (#3383). Ajoute support PHP 8.4. Compat 8.5 OK. |
| `slim/psr7` | `1.7.0` *(pin)* | 1.7.0 | 1.7.0 | ❌ | 🟢 | Dépinner en `^1.7`. |
| `slim/php-view` | `^3.4.0` | 3.4.0 | 3.4.0 | ❌ | 🟢 | — |
| `php-di/php-di` | `^7.0.10` | 7.0.10 | **7.1.1** (16 août 2025) | ❌ mineur | 🟢 | 7.1 apporte support PHP 8.4, pas de break. |
| `nikic/fast-route` | (indir.) | 1.3.0 | 1.3.0 | — | 🟢 | Déjà à jour. |

### 1.2 Bibliothèques Google (point critique)

| Package | `composer.json` | `lock` | Dernière stable | Δ | Impact | Détail |
|---|---|---|---|---|---|---|
| **`google/gax`** | `1.36.0` *(pin strict)* | v1.36.0 | **v1.42.2** (10 avr 2026) | +6 mineures | 🟠 | Requires `php ^8.1`, `google/protobuf ^4.31 \|\| ^5.34`, `guzzlehttp/promises ^2.0`. Pas de BC break majeur documenté. À dépinner en `^1.42`. |
| **`google/protobuf`** | `^v4.30.2` | v4.30.2 | v4.33.6 (4.x) / **v5.34.1** (5.x) | +1 majeure dispo | 🟠 | 5.x requires `php ^8.2`. `gax 1.42` accepte `^4.31 \|\| ^5.34`. Recommandation : passer à `^v5.34`. |
| `google/common-protos` | `^4.12.0` | (via gax) | **v4.14.0** | patch | 🟢 | Suivra automatiquement. |
| **`google/cloud`** (méta) | `v0.286.0` *(pin)* | v0.286.0 | **v0.326.0** (9 avr 2026) | +40 mineures | 🔴 | **Recommandation forte : supprimer ce meta-package.** Le code n'utilise réellement que 5 composants (cf. §3). |
| `google/apiclient` | `^v2.18.3` | v2.18.3 | v2.18.3 | ❌ | 🟢 | — |
| `google/auth` | (indir.) | v1.47.0 | v1.49.x | patch | 🟢 | Auto-bumpé par gax. |
| `grpc/grpc` | `^1.57.0` | 1.57.0 | **1.74.0** | +17 mineures | 🟠 | ext-grpc côté runtime : vérifier match avec le runtime GAE `php85`. |

### 1.3 Dépendances métier

| Package | `composer.json` | `lock` | Dernière stable | Impact |
|---|---|---|---|---|
| `doctrine/annotations` | `^1.14.3` | 1.14.4 | 1.14.4 (2.x existe mais BC) | 🟡 — `2.x` supprime les loaders globaux. À différer. |
| `egulias/email-validator` | `^4.0.4` | 4.0.4 | 4.0.x | 🟢 |
| `kreait/firebase-php` | `^7.18.0` | 7.18.0 | 7.x latest | 🟢 |
| `lcobucci/jwt` | `5.5.0` *(pin)* | 5.5.0 | 5.5.x | 🟢 — dépinner. |
| `nesbot/carbon` | `^3.9.1` | 3.9.1 | 3.10.x | 🟢 |
| `nyholm/psr7` | `^1.8.2` | 1.8.2 | 1.8.x | 🟢 |
| `ramsey/uuid` | `^4.7.6` | 4.7.6 | 4.7.x (5.x dispo) | 🟡 — 5.x breaks, rester sur 4.x. |
| `robmorgan/phinx` | `^0.16.7` | 0.16.8 | **0.16.11** (15 mars 2026) | 🟢 |
| `phpmailer/phpmailer` | `^v6.10.0` | 6.10.0 | 6.10.x | 🟢 |
| `sendgrid/sendgrid` | `^8.1.11` | 8.1.11 | 8.1.x | 🟢 |
| `jolicode/slack-php-api` | `^v4.8.0` | 4.8.0 | 4.x | 🟢 |
| `symfony/http-client` | `^v7.2.4` | 7.2.4 | **7.3.x** / 7.4 LTS | 🟡 |
| `symfony/validator` | `^v7.2.6` | 7.2.6 | 7.3.x / 7.4 LTS | 🟡 |
| `guzzlehttp/guzzle` | `^7.9.3` | 7.9.3 | 7.9.x | 🟢 |
| `zircote/swagger-php` | `^4.11.1` | 4.11.1 | 4.12+ | 🟢 |
| `google/recaptcha` | `^1.3.0` | 1.3.0 | 1.3.x | 🟢 |

---

## 2. Compatibilité PHP 8.3 → 8.5 (cible GAE Standard `php85`)

GAE Standard **supporte PHP 8.5** (release stable 20/11/2025). Extensions actives pertinentes : bcmath, mbstring, intl, mysqli, PDO, OPcache, sodium. Extensions à charger dynamiquement : **grpc**, **protobuf**.

### 2.1 Breaking changes PHP 8.5 à vérifier dans le code RCQ

| Changement | Risque RCQ |
|---|---|
| **Suppression** `disable_classes` INI | 🟢 non utilisé |
| Backtick `` ` `` (shell_exec) deprecated | 🟢 à grep |
| Casts non-canoniques `(boolean)`, `(integer)`, `(double)`, `(binary)` deprecated | 🟡 auditer `server/src` |
| `null` comme array offset / dans `array_key_exists()` deprecated | 🟡 auditer |
| `__sleep()` / `__wakeup()` soft-deprecated (prefer `__serialize`/`__unserialize`) | 🟡 vérifier Entities sérialisables |
| `$exclude_disabled` de `get_defined_functions()` deprecated | 🟢 non utilisé |
| `report_memleaks` INI deprecated | 🟢 non concerné |
| `DateTimeInterface::RFC7231` / `DATE_RFC7231` deprecated | 🟢 à grep |

→ **Action** : ajouter `phpstan/phpstan` + `phpcompatibility/php-compatibility` en dev-deps pour scan en bloc (PR 1).

### 2.2 Typage strict (PHP 8.2 → 8.5)

Les libs modernes (`slim 4.15`, `php-di 7.1`, `gax 1.42`, `google/cloud 0.326`) ciblent toutes `php: ^8.1` et sont PHPStan-level 2+. Aucune contrainte spécifique à 8.5 ne bloque l'update.

---

## 3. Focus Google : suppression du hack `getLabel` dans `index.php`

### 3.1 Chaîne de causalité

Le warning `getLabel is deprecated. Use isRequired or isRepeated instead` provient de :
- `ext-protobuf` ≥ 4.26 marque `getLabel()` deprecated
- `google/gax` ≤ 1.36 (version actuelle) appelle encore `getLabel()` en interne
- ⇒ pollution de la sortie JSON de l'API REST

### 3.2 Résolution

Le fix a été mergé upstream (issues [googleads/google-ads-php#1095](https://github.com/googleads/google-ads-php/issues/1095) + #1106) : toutes les références à `getLabel()` ont été migrées vers `isRequired()` / `isRepeated()` dans le code généré de `gax` et des client libs. Contrainte relaxée en `google/protobuf: ^4.31 || ^5.34`.

**En pratique pour RCQ** :

| Étape | Après upgrade |
|---|---|
| `google/gax` → `^1.42` | Plus aucun appel interne à `getLabel()` |
| `google/protobuf` → `^5.34` (ou `^4.33`) | Plus de warning émis |
| ⇒ **`set_error_handler` dans `server/public/rest/index.php` peut être SUPPRIMÉ** | ✅ |

### 3.3 Composants `google/cloud-*` à extraire du méta-package

Le code importe uniquement :

```
Google\Cloud\Firestore\…         → google/cloud-firestore
Google\Cloud\Logging\…           → google/cloud-logging
Google\Cloud\PubSub\…            → google/cloud-pubsub
Google\Cloud\SecretManager\V1\…  → google/cloud-secret-manager
Google\Cloud\Storage\…           → google/cloud-storage
```

Le méta-package `google/cloud` v0.286 inclut ~150 packages inutilisés (BigQuery, Dataflow, Vision…) qui gonflent `vendor/` de plusieurs centaines de Mo et motivent l'exclusion `classmap` sur `AttributeContext.php`.

**Gain** : `vendor/` réduit de ~400 Mo → ~50 Mo + suppression de l'exclusion classmap.

---

## 4. Ordre d'exécution proposé (4 PRs successives)

### **PR 1 — Tooling & sanity checks** (risque 🟢)

- Ajouter `phpstan/phpstan ^2`, `phpcompatibility/php-compatibility` en dev-deps
- Lancer audit statique sur PHP 8.5 ; logger les findings
- Dépinner les versions exactes → contraintes `^` :
  - `lcobucci/jwt` : `5.5.0` → `^5.5`
  - `slim/psr7` : `1.7.0` → `^1.7`
  - `google/gax` et `google/cloud` : conservés (bump en PR 3)

### **PR 2 — Framework & infra libs** (risque 🟡)

- `slim/slim` → `^4.15.1`
  - Audit usages `setArgument`/`setArguments` (middleware `AuthorisationMiddleware` ?) → remplacer par middleware dédié
- `php-di/php-di` → `^7.1.1`
- `robmorgan/phinx` → `^0.16.11`
- `nesbot/carbon`, `guzzlehttp/guzzle`, `symfony/*`, `doctrine/annotations` : update mineur
- Tests d'intégration locaux via Docker

### **PR 3 — Google SDKs** (risque 🟠, le cœur du sujet)

- Remplacer `"google/cloud": "v0.286.0"` par 5 packages granulaires :

  ```json
  "google/cloud-firestore": "^2.0",
  "google/cloud-logging": "^2.0",
  "google/cloud-pubsub": "^2.19",
  "google/cloud-secret-manager": "^2.3",
  "google/cloud-storage": "^1.51"
  ```

- Bump `google/gax` → `^1.42`
- Bump `google/protobuf` → `^5.34`
- Bump `google/common-protos` → `^4.14`
- Supprimer `exclude-from-classmap` sur `AttributeContext.php`
- **Supprimer le `set_error_handler` `getLabel`** dans `server/public/rest/index.php`
- Vérifier la version de `ext-grpc` dans le Dockerfile pour match avec `grpc/grpc ^1.74`

### **PR 4 — Runtime PHP 8.5** (risque 🟠)

- Dockerfile dev : `FROM php:8.5-fpm-bookworm` (au lieu de 8.3)
- `composer.json` : `"php": "^8.5"` + `"platform": { "php": "8.5" }`
- GAE : `server/app_template.yaml` → `runtime: php85` (valider qu'aucun `php_version` résiduel ne force 8.3)
- Fix des deprecations relevées en PR 1 (casts non-canoniques, `__sleep/__wakeup`, etc.)
- Smoke tests complets + déploiement staging

---

## 5. Risques identifiés et mitigations

| Risque | Mitigation |
|---|---|
| `setArgument()` encore utilisé et break silencieux | `grep -rn "setArgument" server/src` **avant** PR 2 |
| Incompat `ext-grpc` C-extension vs GAE runtime `php85` | Tester sur GAE staging avant prod ; pin précis si besoin |
| Surfaces API Firestore/PubSub v2.x modifiées (namespaces `V1\Client\*` nouveau style vs legacy) | Auditer chaque usage ligne par ligne (le code utilise déjà `V1\Client\SecretManagerServiceClient`, bon signe) |
| Phinx migrations ↔ mysqli PHP 8.5 | Smoke test migration locale en Docker |
| Perf dégradée après split `google/cloud` (autoloading) | `composer dump-autoload -o` + OPcache preload |
| Regression sur le front (CORS/JSON) après suppression du handler d'erreurs | Re-run E2E `client/e2e` après PR 3 |

---

## 6. Fichiers attendus en modification

| PR | Fichiers |
|---|---|
| 1 | `server/composer.json`, `server/composer.lock`, (opt.) `server/phpstan.neon` |
| 2 | `server/composer.json`, `server/composer.lock`, `server/src/Middleware/*`, `server/src/routes/*` (si `setArgument`) |
| 3 | `server/composer.json`, `server/composer.lock`, `server/public/rest/index.php`, `docker/php/Dockerfile` (bump grpc) |
| 4 | `docker/php/Dockerfile`, `docker-compose.yml`, `server/composer.json`, `server/app_template.yaml`, `run_local.sh`, `run_local.md`, `docker.md` |
