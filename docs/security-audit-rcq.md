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
| Dependabot alerts (GitHub) | **56** (2 critical, 19 high, 29 moderate, 6 low) — confirmé au push de la PR #212 |
| GitHub Actions | **aucun** workflow (à introduire en Wave 5) |
| Dependabot/Renovate | non configuré |
| JWT | HS256, secret symétrique en clair en RAM (`InMemory::plainText($jwtSecret)`), `iss`/`aud` validés |
| Headers HTTP (nginx) | ✅ `X-Frame-Options DENY`, ✅ `X-Content-Type-Options nosniff`, ✅ `fastcgi_hide_header X-Powered-By` — ❌ **pas de HSTS, pas de CSP, pas de Referrer-Policy, pas de Permissions-Policy** |
| CORS | aucune ligne `Access-Control-*` côté serveur ni nginx (front + back même origine via Cloud Run → OK par construction, à confirmer pour graph.redcrossquest.com) |
| Postinstall scripts npm | non audité ; 2 deps GitHub-hosted (`dev-mansonthomas/angular-qr-updated`, `angular-qr-scanner-updated`) — fork perso, pinné par **tag** pas SHA |
| Versions à risque connues | AngularJS 1.8.3 (EOL), jQuery 2.2.4 (CVE-2020-11022/23 sur `<2.2.0` mais reste vieux), bootstrap-sass 3.x (BS3 EOL), `angular-jwt@0.1.11` (10+ ans), Gen1 Cloud Functions (Node runtime ?). |

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

### Wave 1 — Supply chain (CRITIQUE)
1. **Composer audit** : résoudre tous les `high`/`critical` sur `composer.lock`. Stack auth-critique : `lcobucci/jwt`, `firebase/php-jwt` transitif éventuel, `kreait/firebase-php`, `guzzlehttp/guzzle`, `symfony/http-client`.
2. **npm audit `client/`** : prioriser les paquets touchant l'exécution navigateur (`angular*`, `jquery`, `bootstrap-sass`, `moment`, `zxcvbn`).
3. **Phase 1.5 worms** : exécuter les 3 scans (Shai-Hulud / Qix / postinstall) sur `client/package-lock.json` et `server/openapi/package-lock.json`.
4. **Forks GitHub** : `dev-mansonthomas/angular-qr-*` → pinner par **commit SHA** au lieu du tag (immuable). Vérifier que les forks n'ont pas dérivé de leur upstream pour autre chose qu'un bump.
5. **Decision point** : AngularJS 1.8.3 EOL + jQuery 2.2.4. Le vrai fix = sortir d'AngularJS (projet hors scope, déjà documenté dans `docs/frontend_upgrade_audit.md`). À court terme : **accepter le risque documenté** et limiter la surface (CSP en Wave 3).

### Wave 2 — Auth, secrets, JWT
1. **JWT** : HS256 acceptable tant que le secret est ≥ 256 bits et stocké uniquement en Secret Manager (déjà le cas). **À vérifier** : longueur effective du secret en prod (`gcloud secrets versions access` sans logger la valeur, juste `wc -c`).
2. **JWT claims** : `exp` court, `iss`/`aud` validés (✅ dans `AuthorisationMiddleware`). Ajouter `nbf` si absent. Vérifier la **rotation** (qu'arrive-t-il si on change le secret en prod — tous les utilisateurs se reconnectent ?).
3. **Firebase admin SDK** : `kreait/firebase-php` 7.18. Vérifier que les tokens RedQuest reçus côté Cloud Functions sont validés (signature + `aud` = projet GCP correct).
4. **Secret Manager** : audit IAM. Chaque service account (Cloud Run runtime, CI futur, Cloud Functions) doit avoir le **rôle minimal** (`secretmanager.versions.access` sur un sous-ensemble de secrets, pas `secretmanager.admin` projet).
5. **ReCaptcha** : `google/recaptcha` configuré — vérifier que la validation backend est obligatoire et non bypass-able (clé secret stockée correctement).
6. **Pas d'OAuth/SSO** côté serveur RCQ (Firebase délègue) → tâche **retirée** du template.
7. **Pas de password storage côté RCQ** (Firebase gère) → tâche **retirée** du template.

### Wave 3 — Surface HTTP & headers
1. **CSP** : ajouter une politique stricte adaptée à AngularJS (besoin de `'unsafe-inline'` pour le bootstrap + `style-src 'self' 'unsafe-inline'`, `script-src 'self'`, `connect-src 'self' https://*.googleapis.com https://www.google.com/recaptcha/`). Tester en **report-only** d'abord en dev.
2. **HSTS** : `Strict-Transport-Security: max-age=31536000; includeSubDomains` — vérifier que Cloud Run ne le pose pas déjà (sinon doublon inoffensif).
3. **Referrer-Policy** : `strict-origin-when-cross-origin`.
4. **Permissions-Policy** : minimum (`camera=(), microphone=(), geolocation=(self)` car le scan QR utilise la caméra → vérifier).
5. **Rate limiting** : nginx `limit_req_zone` sur `/rest/login`, `/rest/registration`, `/rest/queteur/registration/approveQueteur` (anti brute-force + anti-spam d'inscription). Le module `ngx_http_limit_req_module` est dispo par défaut.
6. **CORS** : si tout est même origine, ajouter explicitement `add_header Access-Control-Allow-Origin "$http_origin" always;` UNIQUEMENT pour `graph.redcrossquest.com` si ce subdomain consomme l'API. Sinon **pas de CORS du tout** (plus sûr).
7. **Input validation** : `ClientInputValidatorSpecs` existe déjà ; auditer la couverture sur toutes les routes (corollaire de `composer check:routes` ajouté en commit récent).

### Wave 4 — Données & accès
1. **SQL injection** : confirmer que **100% des requêtes PDO** utilisent des paramètres bindés (`:name`, `?`). Grep des `->query(` vs `->prepare(` + interpolation `"$variable"` dans les SQL.
2. **IDOR / authorisation horizontale** : chaque route doit filtrer par `ulId` issu du JWT, pas du body/URL. Re-vérifier `getQueteurById`, `getTroncQueteurById`, `getULById`, etc.
3. **Authorisation verticale** : `AuthorisationMiddleware` filtre déjà par `roleId` vs path — bon. Mais re-confirmer après la régression `ul_settings` fixée récemment (commit `7ba68b7`) qu'aucun champ sensible ne fuit pour `roleId=1`.
4. **Logging PII/secrets** : audit des `$this->logger->error(...)` qui passent des `$queteurEntity` ou `$messageProperties` — risque de fuite RGPD (email, mobile, NIVOL, birthdate dans les logs Cloud Logging). Définir une whitelist de champs loggables.
5. **RGPD anonymisation** : `AnonymizeQueteur` existe — vérifier qu'il anonymise bien tous les champs PII (notes, email, mobile, birthdate, NIVOL) et que les backups/exports BigQuery suivent.
6. **PubSub payloads** : cf. `docs/pubsub_messages.md` — les 3 topics actifs publient des `QueteurEntity` et `TroncQueteurEntity` **complètes** (incluant email, mobile, NIVOL, birthdate). Vérifier que les abonnés (Cloud Functions + BigQuery sinks) sont dans le périmètre RGPD et que les topics ont DLP scanning si possible.

### Wave 5 — CI/CD & infra (introductive)
1. **Introduire un workflow GitHub Actions minimal** :
   - `composer-audit.yml` : `composer audit --locked` sur PRs et chaque semaine sur master.
   - `npm-audit.yml` : `npm audit --audit-level=high` côté `client/`.
   - `dependabot.yml` : config groupée (npm devDep / npm dep / composer), auto-merge des `patch` uniquement après tests.
   - **Toutes les actions `uses:` doivent être pinnées par SHA 40-char** (anti `tj-actions`).
2. **`permissions:` explicites** sur chaque workflow (`contents: read` par défaut).
3. **Branch protection sur `master`** : review obligatoire (déjà le cas ?), status checks `composer-audit` et `npm-audit` obligatoires, no force push.
4. **Cloud Run** :
   - Service account dédié par service avec rôles minimaux (Cloud SQL Client, Secret Manager Accessor sur secrets précis, PubSub Publisher sur 3 topics seulement).
   - `--ingress=internal-and-cloud-load-balancing` à évaluer si on utilise un load balancer + Cloud Armor.
   - Container : user non-root dans `docker/php-fpm/Dockerfile`, `readOnlyRootFilesystem` si compatible avec PHP (tmp dirs à monter).
5. **Cloud Armor** (optionnel) : règle anti-OWASP préconfigurée + rate limit IP-based en complément de nginx.
6. **Audit IAM cross-project** (rcq-fr-prod ↔ rq-fr-prod) : les Cloud Functions ont besoin d'accès cross-project — vérifier qu'aucun rôle `editor`/`owner` global n'est attribué (cf. `GCP/init_lib/common.sh`).

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
