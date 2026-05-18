# Security Audit — RedCrossQuest (plan d'exécution)

Plan d'audit sécurité **contextualisé** au repo RedCrossQuest, dérivé du template générique `docs/security-audit.md`. **Ce document est un plan, pas une implémentation** : à exécuter wave par wave, avec approbation utilisateur entre chaque wave.

> Tracking : todo « *Security audit RCQ — execute waves from docs/security-audit-rcq.md* ».

---

## 0. Contexte projet (placeholders remplis)

- **Repo** : `/Users/tom/Projects/RedCrossQuest` — branche `master` — remote `github.com/dev-mansonthomas/RedCrossQuest`.
- **Stack** :
  - **Backend** : PHP 8.5 + Slim 4 (`slim/slim ^4.15`) + PHP-DI 7 + `lcobucci/jwt ^5.6` (HS256, symétrique) + `kreait/firebase-php ^7.18` + PDO/MySQL + `phpmailer` + `sendgrid` + Google Cloud SDK (PubSub, Firestore, Secret Manager).
  - **Frontend** : AngularJS 1.8.3 (**EOL depuis dec 2021**), jQuery 2.2.4, bootstrap-sass 3.4.3, Gulp 5, Node 22, ESLint 9. Build statique servi via Cloud Run (front + back même domaine app).
  - **Cloud Functions** : Gen1 historiques (Node) déployées depuis ce repo via `GCP/deploy_cloudFunctions.sh` — **migration en cours vers `/Users/tom/Projects/rcq-functions-v2/` (Python 3.13 Gen2, audit séparé, hors scope ici).**
  - **Infra runtime** : Cloud Run (`runtime: php85` côté GAE template), Cloud SQL MySQL (unix socket), Firestore, PubSub, Secret Manager.
- **Environnements** : 3 projets GCP — `rcq-fr-dev`, `rcq-fr-test`, `rcq-fr-prod`. Déploiement **manuel** via `./gcp-deploy.sh <country> <env> <target>` (cibles : `route`/`front`/`back`/`fb`/`functions`/`all`). **Pas de CI/CD** (`.github/workflows/` absent).
- **Secrets** : `MYSQL_PASSWORD`, clés Firebase, JWT secret, SendGrid API key, ReCaptcha, etc. — tous récupérés **runtime** via `SecretManagerService` (jamais en clair dans le repo). `.env` local git-ignored, `.env.example` propre.

---

## 1. Recon — état des lieux (read-only, déjà fait pour ce plan)

| Élément | Valeur |
|---|---|
| Lockfiles | `client/package-lock.json`, `server/composer.lock`, `server/openapi/package-lock.json` |
| Dependabot alerts baseline (PR #212) | **56** (2 critical, 19 high, 29 moderate, 6 low) |
| Dependabot alerts après Wave 1 (attendu post-scan) | **~17** — uniquement Angular EOL + legacy gulp 3.x devDep |
| `composer audit` après Wave 1 | **clean** (0 advisory) |
| `npm audit` (client/) après Wave 1 | **12** (0 crit / 7 high / 5 mod / 0 low) — 100 % legacy gulp + Angular EOL |
| GitHub Actions | **aucun** workflow (à introduire en Wave 5) |
| Dependabot/Renovate | non configuré |
| JWT | HS256, secret symétrique en clair en RAM (`InMemory::plainText($jwtSecret)`), `iss`/`aud` validés |
| Headers HTTP (nginx) | ✅ `X-Frame-Options DENY`, ✅ `X-Content-Type-Options nosniff`, ✅ `fastcgi_hide_header X-Powered-By` — ❌ **pas de HSTS, pas de CSP, pas de Referrer-Policy, pas de Permissions-Policy** |
| CORS | aucune ligne `Access-Control-*` côté serveur ni nginx (front + back même origine via Cloud Run → OK par construction, à confirmer pour graph.redcrossquest.com) |
| Postinstall scripts npm | non audité ; 2 deps GitHub-hosted (`dev-mansonthomas/angular-qr-updated`, `angular-qr-scanner-updated`) — fork perso, pinné par **tag** pas SHA |
| Versions à risque connues | AngularJS 1.8.3 (EOL), bootstrap-sass 3.x (BS3 EOL), `angular-jwt@0.1.11` (10+ ans), Gen1 Cloud Functions (Node runtime ?). *jQuery passé à 3.7.1 en B4.* |

---

## 2. Phase 1.5 — Supply chain : applicabilité aux paquets RCQ

| Attaque (template) | Applicable à RCQ ? | Raison |
|---|---|---|
| **Shai-Hulud** (sept 2025, `@ctrl/tinycolor`, `@nativescript-community`…) | ⚠️ **à vérifier** | aucun de ces paquets visible directement dans `client/package.json` mais transitifs possibles via `gulp-*`, `karma-*` → grep `npm ls` requis |
| **s1ngularity / nx** (août 2025) | ❌ non applicable | pas de `nx` ni `@nrwl/*` dans le repo |
| **Qix / chalk** (sept 2024) | ⚠️ **à vérifier** | `chalk ^4.1.2` présent en devDep — version OK, mais transitifs de `gulp-*` à vérifier (`debug`, `ansi-styles`, etc.) |
| **lottie-player** (oct 2024) | ❌ non applicable | pas utilisé |
| **ua-parser-js** (2021) | ⚠️ transitif potentiel | grep requis dans `package-lock.json` |
| **node-ipc** (2022) | ❌ non applicable | pas direct, transitif possible |
| **xz-utils CVE-2024-3094** | ⚠️ **à vérifier sur Docker images** | nos images `docker/php-fpm` et `docker/node` reposent sur quoi ? (alpine/debian-slim) → audit base images |
| **tj-actions/changed-files** (mars 2025) | ❌ non applicable | pas de workflows GitHub Actions… **mais** devient applicable dès qu'on en ajoute en Wave 5 |

---

## 3. Plan par waves — adapté RCQ

### Wave 0 — Baseline exécuté (2026-05-17)

#### Dependabot (`gh api /repos/.../dependabot/alerts?state=open`)

| Écosystème | critical | high | medium | low | total |
|---|---|---|---|---|---|
| npm | 2 | 18 | 29 | 6 | **55** |
| composer | 0 | 1 | 0 | 0 | **1** |
| **Total** | **2** | **19** | **29** | **6** | **56** |

**Paquets agrégés** (par worst severity) :

| # alerts | sev | écosystème | paquet | first_patch | rel | manifest |
|---|---|---|---|---|---|---|
| 6 | critical | npm | `lodash` | 4.17.12 | transitive | `client/package-lock.json` |
| 2 | critical | npm | `minimist` | 0.2.4 | transitive | `client/package-lock.json` |
| 1 | high | composer | `phpseclib/phpseclib` | **3.0.52** | transitive | `server/composer.lock` |
| 15 | high | npm | `axios` | 0.31.1 | transitive | `client/package-lock.json` |
| 11 | high | npm | `angular` | — *(EOL, pas de patch)* | transitive | `client/package-lock.json` |
| 5 | high | npm | `minimatch` | 3.1.3 | transitive | `client/package-lock.json` |
| 1 | high | npm | `semver` | 5.7.2 | transitive | `client/package-lock.json` |
| 1 | high | npm | `braces` | 3.0.3 | transitive | `client/package-lock.json` |
| 1 | high | npm | `lodash.template` | — *(dead pkg)* | transitive | `client/package-lock.json` |
| 1 | high | npm | `merge` | 2.1.1 | transitive | `client/package-lock.json` |
| 4 | medium | npm | `jquery` | 3.5.0 | **direct** | `client/package-lock.json` |
| 1 | medium | npm | `angular-sanitize` | — | **direct** | `client/package-lock.json` |
| 2 | medium | npm | `postcss` | 8.5.10 | transitive | `client/package-lock.json` |
| 1 | medium | npm | `micromatch` | 4.0.8 | transitive | `client/package-lock.json` |
| 2 | medium | npm | `swagger-ui-dist` | 4.1.3 | **direct** | `server/openapi/package-lock.json` |
| 1 | low | npm | `send` | 0.19.0 | transitive | `client/package-lock.json` |
| 1 | low | npm | `serve-static` | 1.16.0 | transitive | `client/package-lock.json` |

#### `composer audit --no-dev` (server/)

```
Found 1 security vulnerability advisory affecting 1 package:
  Package : phpseclib/phpseclib
  Severity: high
  CVE     : CVE-2026-44167  (CVE-2024-27355 mitigation bypass — OID amplification DoS in ASN1::decodeOID())
  Affects : >=3.0.0,<=3.0.51
  Fix     : 3.0.52
```
**100 % alignement** avec Dependabot #288.

#### `npm audit` (client/, exécuté via `node-client`)

```
critical: 5   (gulp-ng-annotate, lodash, minimist, ng-annotate, optimist — chaîne devDep gulp-ng-annotate)
high:    21
moderate: 12
low:      2
total:   40
```
40 < 56 car npm regroupe les CVE par paquet, là où Dependabot publie une alerte par advisory. **Aucune divergence sur les paquets critiques**.

Note : `gulp-ng-annotate` est en **devDep** uniquement (build pipeline). Les 5 « critical » remontés par npm audit sont donc côté tooling et n'arrivent jamais dans le bundle navigateur. À traiter quand même mais avec une priorité moindre que `axios`/`lodash` runtime.

#### Buckets de remédiation (proposés pour Wave 1)

| Bucket | Sévérité | Effort | Action proposée |
|---|---|---|---|
| **B1 — `phpseclib 3.x → 3.0.52`** | high | XS (`composer update`) | 1 PR. Une seule alerte composer, fix transitif clean. |
| **B2 — `axios` lockfile bump** | high | S | 15 alertes sur un seul paquet transitif. `npm update axios --depth=N` ou override dans `package.json`. |
| **B3 — `lodash`, `minimist`, `braces`, `semver`, `minimatch`, `merge`, `micromatch`, `postcss`, `send`, `serve-static`** (chaîne gulp/karma) | critical→low | M | toolchain devDep. Un seul `npm-force-resolutions` ou ciblage `gulp-ng-annotate@latest` peut purger plusieurs ligues d'un coup. **Test obligatoire** : `npm ci && gulp build`. |
| **B4 — `jquery 2.2.4 → 3.5.0+`** | medium | **L** (risque régression front) | Direct dep, AngularJS 1.8.x compatible avec jQuery 3 mais zone à tester (tooltips, modales, datepickers). |
| **B5 — `swagger-ui-dist 3.x → 4.1.3+`** | medium | S | iso-fonctionnel pour la doc OpenAPI servie par `server/openapi/`. |
| **B6 — `angular 1.8.3` + `lodash.template` + `angular-sanitize`** | high→medium | ❌ pas de fix | EOL, **risque accepté** + mitigation par CSP en Wave 3. |
| **B7 — `gulp-ng-annotate@0.2.0 → 0.3.0`** (npm audit only) | critical | S devDep | bumper la version dans `package.json` (autorisé : devDep, hors contrainte « pas de modif `package.json` runtime »). |

#### Docker base images
- `docker/php/Dockerfile`: `php:${PHP_VERSION:-8.5}-fpm-bookworm` → Debian 12, xz 5.4.1-1 ✅
- `docker/node/Dockerfile`: à pinner par digest en Wave 1 (cf. recommandation worm scan).
- `docker/nginx/Dockerfile`: `nginx:*-alpine` → pas de liblzma.

#### Décisions découlant de ce baseline
- ✅ La **Wave 1.5 worm scan** était CLEAN — confirmation que les 56 alertes Dependabot ne contiennent **aucune compromission supply chain active** (toutes des CVE classiques, sans backdoor).
- ✅ La clé `ul_update_topic` a été retirée du `server/src/settings.php` local (Task B). `settings.php` étant gitignored et copié depuis `~/.cred/rcq-${COUNTRY}-${ENV}-settings.php` au déploiement, l'opérateur doit propager la même suppression dans les trois fichiers `~/.cred/rcq-fr-{dev,test,prod}-settings.php`. Voir `docs/pubsub_messages.md` section D.
- 🟢 **Wave 1 prête à démarrer** : commencer par **B1 (phpseclib)** comme dry-run du process (1 paquet, fix mineur, faible risque).

### Wave 1 — Supply chain — ✅ **DONE (2026-05-18)**

#### Bilan d'exécution

| Bucket | PR | Commit | Net advisories | Statut |
|---|---|---|---|---|
| **B1** — `phpseclib 3.0.51 → 3.0.52` (CVE-2026-44167) | [#215](https://github.com/dev-mansonthomas/RedCrossQuest/pull/215) | `82483b9` | composer audit −1 HIGH → **clean** | ✅ |
| **B2** — `axios ≥ 0.31.1` via `overrides` (chaîne `angular-audio → browser-sync@2 → localtunnel → axios@0.21.4`) | [#216](https://github.com/dev-mansonthomas/RedCrossQuest/pull/216) | `80f6743` | npm 40 → 38 / Dependabot −15 HIGH | ✅ |
| **B3** — toolchain gulp via 8 `overrides` (braces, micromatch, minimatch, semver, send, serve-static, lodash, globule) | [#217](https://github.com/dev-mansonthomas/RedCrossQuest/pull/217) | `8683dcb` | npm 38 → 18 (**−20**) | ✅ |
| **B4** — `jquery ^2.2.4 → ^3.7.1` (XSS, proto pollution) | [#218](https://github.com/dev-mansonthomas/RedCrossQuest/pull/218) | `7d12cd6` | npm 18 → 17 (jquery cleared) | ✅ |
| **B5** — `swagger-ui-dist ^3.25.0 → ^5.32.6` (SSRF, spoofing) | [#219](https://github.com/dev-mansonthomas/RedCrossQuest/pull/219) | `68b566d` | Dependabot −2 medium | ✅ |
| **B6** — Angular 1.8.3 + lodash.template + angular-sanitize | — | — | **risque accepté** — pas de fix upstream (EOL), mitigation Wave 3 (CSP) | 🟡 |
| **B7** — chaîne `gulp-ng-annotate` via overrides `minimist^1.2.8` + `merge^2.1.1` | [#220](https://github.com/dev-mansonthomas/RedCrossQuest/pull/220) | `cb83dbc` | npm 17 → 12 (**−4 critical, 0 restants**) | ✅ |

**Décompte avant/après Wave 1 (npm audit `client/`)** :
```
avant : 2 low / 12 mod / 21 high / 5 critical  = 40 advisories
après : 0 low /  5 mod /  7 high / 0 critical  = 12 advisories
delta : −2     −7        −14       −5          = −28 advisories
```

**Décompte avant/après Wave 1 (`composer audit` server/)** :
```
avant : 1 advisory (phpseclib HIGH)
après : 0 advisory (clean)
```

#### Resté ouvert après Wave 1 (= risque accepté)

Les 12 advisories `npm audit` restantes sont **toutes** dans deux catégories pour lesquelles il n'existe **aucun fix upstream** :

1. **Legacy gulp 3.x build toolchain (devDep, hors bundle navigateur)** — `gulp@3.9.1`, `gulp-util`, `gulp-htmlmin`, `html-minifier`, `lodash.template`, `gulp-sourcemaps` + `postcss@7` (`@gulp-sourcemaps/identity-map`). Ces paquets sont morts upstream (aucune release depuis 2019+). Le seul vrai fix = migrer hors de Gulp (gros chantier, hors scope sécu).
2. **AngularJS 1.8.3 EOL** — `angular`, `angular-audio`, `angular-sanitize`, `angular-qr-scanner-updated` (fork perso). Mitigation : **CSP stricte en Wave 3** (réduit la surface XSS) + sortie d'AngularJS planifiée dans `docs/frontend_upgrade_audit.md` (hors scope sécu).

#### Reporté (sera traité hors Wave 1)

- **Forks GitHub pinning par SHA** (`dev-mansonthomas/angular-qr-updated`, `angular-qr-scanner-updated`) — déplacé dans **Wave 5 / supply chain housekeeping** (geste d'hygiène, faible risque, pas lié à une CVE active).
- **Docker base images** : `node-client` est sur Debian 12 + bookworm via `node:22`. Pinning par digest à introduire dans **Wave 5 / CI/CD**.
- **`composer audit --dev`** (incluant devDeps) — non exécuté en Wave 1 (le baseline était `--no-dev`). À faire en Wave 5 dans le workflow `composer-audit.yml`.

### Wave 2 — Auth, secrets, JWT — 🟢 À FAIRE

**Objectif** : durcir la chaîne d'authentification (JWT RCQ + Firebase + Secret Manager) et figer l'IAM GCP au principe du moindre privilège.

**Périmètre code** :
- `server/src/Middleware/AuthorisationMiddleware.php` (validation JWT + ACL par roleId)
- `server/src/routes/routesActions/authentication/AuthenticateAction.php` (login RCQ)
- `server/src/routes/routesActions/authentication/FirebaseAuthenticateAction.php` (login Firebase)
- `server/src/Service/SecretManagerService.php` (accès Secret Manager)
- `server/src/Service/ClientInputValidator/` (validation ReCaptcha)
- `server/src/routes/00-authentication.php` (5 routes auth publiques)

| # | Tâche | Effort | Critère de succès |
|---|---|---|---|
| **W2-1** | **Audit longueur secret JWT en prod** : `gcloud secrets versions access latest --secret=JWT_SECRET --project=rcq-fr-prod \| wc -c` (≥ 32 bytes = 256 bits). Idem dev/test. **Ne jamais logger la valeur.** | XS | Trois envs ≥ 32 bytes confirmés, document privé updaté. |
| **W2-2** | **Claims JWT** : confirmer `exp` (≤ 8h ? à arbitrer), `iss`/`aud` validés (déjà OK dans `AuthorisationMiddleware`), ajouter `nbf` si absent. | S | Token décodé en test contient les 4 claims. |
| **W2-3** | **Rotation secret JWT** : documenter la procédure (Secret Manager versioning + redeploy Cloud Run). Tester la rotation en `rcq-fr-dev` (tous les users actifs sont déconnectés, sans crash backend). | S | Runbook dans `docs/runbooks/jwt-rotation.md`. |
| **W2-4** | **Firebase token validation** : `kreait/firebase-php@7.18` — vérifier dans `FirebaseAuthenticateAction` que `$auth->verifyIdToken()` est appelé (signature + `aud` = projet GCP correct, pas seulement parse). PHPStan signale déjà un type mismatch `$JWTConfiguration` ligne 78 (`docs/audit/phpstan.txt`:1655) — investiguer. | M | Test unitaire avec token forgé → rejeté. |
| **W2-5** | **Audit IAM Secret Manager** : pour chaque SA (`rcq-fr-{dev,test,prod}-backend@…`, Cloud Functions SA, futur CI SA), grep `roles/secretmanager.*` → doit être `roles/secretmanager.secretAccessor` sur secrets **précis** (pas `admin` projet). Commande : `gcloud projects get-iam-policy rcq-fr-prod --flatten="bindings[].members" --filter="bindings.role:roles/secretmanager.*"`. | M | Tableau IAM par SA dans `docs/runbooks/iam-audit.md`. |
| **W2-6** | **ReCaptcha v3 backend** : confirmer que `AuthenticateAction` rejette **systématiquement** si `ReCaptchaService::verify()` échoue (pas de fallback silencieux). PHPStan ligne 32 (`FirebaseAuthenticateAction::$reCaptchaService is never read`) → la prop est injectée mais jamais utilisée, **possible bypass**. | S | Trace de log explicite sur échec ReCaptcha, refactor pour utiliser la prop. |
| **W2-7** | **Audit des 5 routes non authentifiées** (`/firebase-authenticate`, `/authenticate`, `/sendInit`, `/getInfoFromUUID/{uuid}`, `/resetPassword`, `/thanks_mailing`, `/ul_registration`) : confirmer rate-limit (W3-5) + validation input + protection anti-énumération sur `/getInfoFromUUID`. | M | Cf. allowlist nginx `docker/nginx/rcq-backend.conf:29`. |
| **W2-8** | **Logging anti-fuite secrets** : grep des `$this->logger->error("...$tokenStr...")` ou similaire — confirmer qu'aucun secret n'est jamais loggé même en debug. | S | Grep clean. |
| ~~OAuth/SSO~~ | Firebase délègue → tâche retirée du template. | — | — |
| ~~Password storage~~ | Firebase gère côté front, backend RCQ ne stocke pas de mot de passe (sauf legacy `q_user.password` à confirmer) → vérifier algo (bcrypt/argon2) si présent. | S | Grep `password_hash` dans backend. |

### Wave 3 — Surface HTTP & headers — 🟢 À FAIRE

**Objectif** : durcir les headers HTTP côté nginx (Cloud Run) et brider les routes auth publiques contre le brute-force.

**Périmètre code** :
- `docker/nginx/rcq-backend.conf` (vhost backend, déjà `X-Frame-Options DENY` + `nosniff`)
- `app.yaml` / config GAE équivalente côté front (à vérifier)
- `server/src/Service/ClientInputValidator/ClientInputValidatorSpecs.php` (couverture validation)

| # | Tâche | Effort | Critère de succès |
|---|---|---|---|
| **W3-1** | **CSP en report-only** sur `rcq-fr-dev` : `Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https://*.googleapis.com https://www.google.com/recaptcha/ https://www.gstatic.com; frame-src https://www.google.com/recaptcha/; object-src 'none'; base-uri 'self'; report-uri /rest/csp-report;`. AngularJS 1.8.x requiert `'unsafe-eval'` (compilation templates) → impossible à éviter sans rewriter. | M | 0 violation CSP en navigation dev ≥ 48h. |
| **W3-2** | **CSP enforce** sur `rcq-fr-test` puis `rcq-fr-prod`, après 1 semaine en report-only sans incident. | S | Même header sans le `-Report-Only`. |
| **W3-3** | **HSTS** : `Strict-Transport-Security: max-age=31536000; includeSubDomains` dans `docker/nginx/rcq-backend.conf` (vérifier que Cloud Run/GFE ne le pose pas déjà — doublon inoffensif mais à confirmer). | XS | Header présent en réponse `curl -I`. |
| **W3-4** | **Referrer-Policy** + **Permissions-Policy** : `Referrer-Policy: strict-origin-when-cross-origin`. `Permissions-Policy: camera=(self), microphone=(), geolocation=(), interest-cohort=()`. La caméra est nécessaire pour le scan QR (`angular-qr-scanner-updated`). | XS | Headers présents. |
| **W3-5** | **Rate limiting nginx** : `limit_req_zone $binary_remote_addr zone=auth:10m rate=5r/m;` + `limit_req zone=auth burst=10 nodelay;` sur les 5 routes publiques (`/rest/authenticate`, `/rest/firebase-authenticate`, `/rest/sendInit`, `/rest/getInfoFromUUID`, `/rest/resetPassword`, `/rest/thanks_mailing`, `/rest/ul_registration`). Module `ngx_http_limit_req_module` dispo par défaut. | M | Test : `for i in $(seq 1 20); do curl -X POST .../authenticate; done` → 503 après 5. |
| **W3-6** | **CORS** : front + back même origine via Cloud Run (`app.redcrossquest.com` / `*.appspot.com`) → **ne rien ajouter**. À confirmer pour `graph.redcrossquest.com` si ce subdomain appelle `/rest/`. Si oui, allowlist explicite (pas de `*`). | XS | `grep -r "Access-Control" docker/nginx/` clean ou allowlist précise. |
| **W3-7** | **Input validation coverage** : `composer check:routes` confirme déjà que toutes les routes ont un `ClientInputValidatorSpecs`. Auditer manuellement les specs des 7 routes publiques (W2-7) — type, longueur max, regex, sanitization. | S | Toutes specs publiques ont `MaxLength` + `Regex` ou `Type` strict. |
| **W3-8** | **CSP report endpoint** : `POST /rest/csp-report` → handler minimal qui log dans Cloud Logging (avec rate-limit pour éviter le DoS log). Optionnel mais utile pour W3-1. | S | Endpoint répond 204, log structuré. |

### Wave 4 — Données & accès — 🟢 À FAIRE

**Objectif** : confirmer l'absence d'injection SQL, d'IDOR/escalade horizontale, et minimiser l'exposition PII (logs, PubSub, BigQuery).

**Périmètre code** :
- `server/src/Service/*DBService.php` (toutes les classes `*DBService` = couche PDO)
- `server/src/Middleware/AuthorisationMiddleware.php` (déjà filtre par `roleId`)
- `server/src/routes/routesActions/**/*.php` (vérifier que `$ulId = $decodedJWT->getUlId();` est utilisé pour filtrer, pas `$args['ulId']`)
- `server/src/Service/AnonymizeQueteur.php`
- `server/src/Service/PubSubService.php` + 3 topics actifs (`mailing`, `mailing_status`, `queteur_anonymization`)

| # | Tâche | Effort | Critère de succès |
|---|---|---|---|
| **W4-1** | **SQL injection sweep** : `grep -rn "->query(" server/src/Service/` (vs `->prepare()`), `grep -rEn '"\\\$[a-zA-Z]' server/src/Service/*DBService.php` (interpolation dans SQL). Toute query non préparée → réécrire en bindings. | M | Grep clean, ou ticket pour chaque cas légitime (rare). |
| **W4-2** | **IDOR horizontal** : pour chaque route lisant un `id` queteur/tronc/ul depuis l'URL ou le body, vérifier que la query ajoute `AND ulId = :ulIdFromJWT`. Lister les routes concernées via `composer check:routes`. | L | Tableau route → check ulId dans `docs/audit/idor.md`. |
| **W4-3** | **Escalade verticale post-`ul_settings`** : `AuthorisationMiddleware` filtre par path/roleId (commit `7ba68b7` a corrigé une régression). Re-tester manuellement pour `roleId=1` (queteur de base) sur les routes admin (`/rest/9/...`, `/rest/8/...`). | S | 0 route admin accessible à roleId=1. |
| **W4-4** | **Logging PII/secrets** : grep `$this->logger->error/warning/info` qui passent un `$queteurEntity`, `$user`, `$messageProperties`, body brut → risque RGPD (email, mobile, NIVOL, birthdate visibles dans Cloud Logging). Définir whitelist (id, ulId, roleId, action). | M | Refactor + tests, plus aucun PII dans les logs. |
| **W4-5** | **AnonymizeQueteur** : vérifier que **tous** les champs PII sont anonymisés (notes, email, mobile, birthdate, NIVOL, firstName, lastName). Vérifier qu'aucune table secondaire (`q_user_action_history`, BigQuery sinks) ne conserve les valeurs originales après anonymisation. | M | Test unitaire `AnonymizeQueteur` couvre tous les champs. |
| **W4-6** | **PubSub payloads minimaux** : actuellement (cf. `docs/pubsub_messages.md`) les 3 topics publient `QueteurEntity`/`TroncQueteurEntity` **complets** (PII inclus). Évaluer s'il est possible de ne publier que `{ulId, queteurId, actionType}` + un fetch séparé côté subscriber. Sinon vérifier que les subscribers/sinks BigQuery sont DPA-conformes. | L | Décision documentée, et si réduction possible, schéma minimisé. |
| **W4-7** | **DLP scanning PubSub** : activer `Cloud DLP` sur les 3 topics si réduction (W4-6) impossible. Alternative : encryption au niveau application avec CMEK. | M | Job DLP planifié, alertes configurées. |

### Wave 5 — CI/CD & infra — 🟢 À FAIRE

**Objectif** : introduire un gating CI minimal (composer/npm audit en bloquant) et durcir l'IAM/runtime GCP.

**Périmètre code** :
- `.github/workflows/` (à créer)
- `.github/dependabot.yml` (à créer)
- `docker/php-fpm/Dockerfile`, `docker/node/Dockerfile`, `docker/nginx/Dockerfile`
- `GCP/init_lib/common.sh`, `GCP/deploy_back.sh`, `GCP/deploy_cloudFunctions.sh`

| # | Tâche | Effort | Critère de succès |
|---|---|---|---|
| **W5-1** | **`composer-audit.yml`** : sur PR et hebdo sur master, `composer install --no-dev` + `composer audit --locked` + `composer audit:php85` (phpstan, phpcs:compat, check:routes). Fail si nouvelle alerte HIGH+. **Actions pinnées par SHA 40-char**. `permissions: contents: read`. | M | Workflow vert, bloque les PRs introduisant une CVE HIGH. |
| **W5-2** | **`npm-audit.yml`** : sur PR et hebdo, `cd client && npm ci && npm audit --audit-level=high --omit=dev`. Idem `server/openapi/`. Idem actions pinnées + permissions minimales. | M | Workflow vert. |
| **W5-3** | **`gulp-build.yml`** : `cd client && npm ci && npx gulp build` pour valider que les overrides Wave 1 ne cassent pas la build (validation que les smoke tests B3/B4 passent). | S | Build vert. |
| **W5-4** | **`.github/dependabot.yml`** : ecosystem `composer` (server/), `npm` (client/, server/openapi/), `docker` (docker/*/Dockerfile), `github-actions` (.github/workflows/). Groupes : `npm-devDep`, `npm-dep`, `composer-direct`, `composer-transitive`. Auto-merge **patch uniquement** après CI verte. | S | Renovate-style PRs hebdo. |
| **W5-5** | **Branch protection `master`** : review obligatoire ≥ 1, status checks `composer-audit` / `npm-audit` / `gulp-build` requis, pas de force push, pas de bypass admin. | XS | Settings GitHub appliqués. |
| **W5-6** | **Pin GitHub-hosted forks par SHA** (reporté de Wave 1) : `angular-qr-updated#v1.0.1` → `angular-qr-updated#<commit-sha>`. Idem `angular-qr-scanner-updated`. Auditer le diff du fork vs upstream. | S | `client/package.json` pinné par SHA. |
| **W5-7** | **Pin Docker base images par digest** : `node:22 → node:22@sha256:...`, `php:8.5-fpm-bookworm → php:8.5-fpm-bookworm@sha256:...`, `nginx:*-alpine → ...@sha256:...`. Renovate gère le bump auto. | S | Images pinnées dans 3 Dockerfiles. |
| **W5-8** | **Cloud Run IAM least-privilege** : pour chaque service Cloud Run (`back`, `front`, `fb`), SA dédié avec **uniquement** `roles/cloudsql.client`, `roles/secretmanager.secretAccessor` sur secrets précis, `roles/pubsub.publisher` sur 3 topics. Pas d'`editor`/`owner`/`viewer` projet. | M | Tableau IAM par service dans `docs/runbooks/iam-audit.md`. |
| **W5-9** | **Cloud Run runtime hardening** : user non-root dans `docker/php-fpm/Dockerfile` (`USER 1000:1000`), `readOnlyRootFilesystem` si compatible PHP-FPM (volumes `tmpfs` pour `/tmp`, `/var/log/...`). Évaluer `--ingress=internal-and-cloud-load-balancing` si Cloud Armor adopté. | M | Container démarre en non-root, tests E2E verts. |
| **W5-10** | **Cloud Armor** (optionnel) : policy anti-OWASP préconfigurée + rate-limit IP (complète W3-5 qui agit par instance). | M | Politique attachée au Load Balancer. |
| **W5-11** | **Audit IAM cross-project** : `rcq-fr-prod` ↔ `rq-fr-prod` (Cloud Functions Gen1 accèdent à des ressources cross-project). Vérifier `GCP/init_lib/common.sh` — aucun rôle `editor`/`owner` global. | S | Tableau cross-project dans `docs/runbooks/iam-audit.md`. |
| **W5-12** | **Reactiver `composer audit --dev`** dans le workflow `composer-audit.yml` (W5-1 le fait déjà mais à expliciter — couvre les devDeps `phpstan`, `phpcs`, `phpunit` si réintroduit, etc.). | XS | Workflow audit complet. |

---

## 4. Hors scope explicite

- **`/Users/tom/Projects/rcq-functions-v2/`** : audit séparé à planifier dans ce repo (stack Python 3.13, Cloud Functions Gen2). Le présent plan ne couvre que les Cloud Functions Gen1 héritées dans RCQ (toujours via `GCP/deploy_cloudFunctions.sh`).
- **Migration AngularJS → framework moderne** : projet à part entière (cf. `docs/frontend_upgrade_audit.md`), traité ici uniquement par **mitigation** (CSP, audit deps, accepter le risque).
- **Migration MySQL → autre** : pas dans cet audit.
- **Pentest applicatif externe** : ce plan est un audit *internal review* + supply chain, pas un test d'intrusion.

---

## 5. Démarrage

Quand l'utilisateur donne le go :
1. **Wave 0** d'abord (~30 min, zéro modif code, juste capture du baseline).
2. **STOP** → présenter résultats Wave 0 + plan Wave 1 ajusté → attendre approbation.
3. **Wave 1** : une PR par groupe de fix (1 PR composer + 1 PR npm + 1 PR forks GitHub).
4. Pour les waves suivantes : présenter chaque wave, demander approbation, exécuter.

**Règle dérivée du template, durcie pour RCQ** : **aucune modif prod sans dev+test d'abord**. Les changements CSP/CORS/rate-limiting doivent être déployés en `dev` puis observés ≥ 24h avant `test`, idem avant `prod`.
