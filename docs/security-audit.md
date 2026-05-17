# Audit sécurité — supply chain + hardening classique

Ce document est un **prompt prêt à coller** pour lancer un audit sécurité complet sur un projet logiciel. Il couvre deux volets : attaques supply chain (npm/PyPI/GitHub Actions, y compris les compromissions récentes Shai-Hulud, nx, chalk/Qix, tj-actions, xz-utils) et hardening classique (auth, secrets, CORS, headers, injection, autorisation, CI/CD).

**Comment l'utiliser** : créer un nouveau workspace Intent sur le repo cible, coller ce fichier (ou son contenu) au coordinator, remplir les 3 placeholders du bloc « Contexte projet », puis laisser le coordinator faire la phase Recon et planifier les waves. Le coordinator ne code pas lui-même : il délègue à des implementors et fait vérifier par des verifiers.

---

## Contexte projet
- **Repo** : <chemin local ou URL git>
- **Stack** : <ex: Node/Angular + Python/FastAPI + MySQL, ou autre — laisse vide pour auto-détection>
- **Environnements** : <local, dev, test, prod — décris brièvement comment ils sont déployés>

## Objectif
Faire un audit sécurité complet en deux volets :
1. **Supply chain** — détecter et corriger toute dépendance compromise, obsolète ou vulnérable, et durcir l'intégrité des lockfiles.
2. **Hardening classique** — corriger les vulnérabilités courantes (auth, secrets, CORS, headers, injection, validation, etc.).

Tu es **Coordinator**. Tu planifies, délègues à des implementors, vérifies. Tu **n'écris pas de code toi-même**. Tu utilises des **waves** (vague d'implémentation → vérification → vague suivante).

## Phase 1 — Recon (toi, sans déléguer)

Avant de planifier, recense :
- Lockfiles présents : `package-lock.json`, `yarn.lock`, `pnpm-lock.yaml`, `poetry.lock`, `Pipfile.lock`, `requirements.txt`, `go.sum`, `Cargo.lock`, `composer.lock`, etc.
- Gestionnaires de secrets : `.env`, `.env.example`, fichiers Terraform, GitHub Actions secrets référencés.
- Frameworks d'auth en place (JWT, OAuth, sessions, cookies).
- Surface réseau : routes API publiques, CORS config, reverse proxy, headers HTTP.
- CI/CD : workflows GitHub Actions / GitLab CI / autre — vérifier pinning des actions tierces.

Lance ces commandes (lecture seule) :
```bash
# Node
npm audit --json 2>/dev/null | head -200
npm ls --all 2>&1 | head -50

# Python
poetry show --outdated 2>/dev/null || pip list --outdated
pip-audit 2>/dev/null || safety check 2>/dev/null

# Secrets dans le repo (sans logger les valeurs)
git log --all -p | grep -iE "(api[_-]?key|secret|password|token|bearer)" | head -20

# Lockfile drift
git diff HEAD -- '*.lock' 'package-lock.json' 2>&1 | head -20
```

Liste ensuite ce que tu trouves dans le Spec **sans révéler de valeurs secrètes** (uniquement les noms de fichiers / paquets / advisories).

## Phase 1.5 — Attaques supply chain connues à vérifier explicitement

Pour chaque attaque ci-dessous, l'implementor DOIT vérifier que le repo n'est pas affecté (lockfile + arbre de dépendances transitives) et documenter le résultat dans le Spec.

### npm — 2025

**Shai-Hulud worm** (septembre 2025)
- Worm npm auto-réplicant qui vole les tokens des maintainers et republie les paquets compromis.
- Plus de 500 paquets touchés sur plusieurs vagues.
- IoCs : présence du fichier `bundle.js` malveillant, scripts `postinstall` exfiltrant `~/.npmrc`, `~/.aws`, `~/.config/gh`, variables d'env.
- Cibles connues incluent des paquets de `@ctrl/*`, `@nativescript-community/*`, `@crowdstrike/*`, plus rebonds via tokens volés.
- Commandes de vérif :
  ```bash
  # Chercher les versions affectées dans le lockfile (liste à mettre à jour selon advisory GitHub)
  npm ls --all 2>&1 | grep -iE "@ctrl/tinycolor|@nativescript-community"
  # Vérifier qu'aucun postinstall n'exfiltre quoi que ce soit
  find node_modules -name "package.json" -exec grep -l "postinstall" {} \; | head
  ```

**s1ngularity / nx attack** (août 2025)
- `nx` et plusieurs plugins (`@nx/*`, `@nrwl/*`) compromis.
- Versions malveillantes : `nx@20.9.0`, `21.5.0`, `21.6.0`, `21.7.0`, `21.8.0`, et plugins associés sur la même fenêtre.
- IoCs : script `telemetry.js` exfiltrant tokens GitHub/npm.
- Vérif : `npm ls nx @nx/* @nrwl/* 2>&1 | grep -v "deduped"`.

**chalk / debug / qix maintainer** (septembre 2024, toujours d'actualité dans les vieux lockfiles)
- Le maintainer Qix s'est fait phisher, ~20 paquets compromis pendant ~2h : `chalk`, `debug`, `ansi-styles`, `strip-ansi`, `color-convert`, `color-name`, `wrap-ansi`, `supports-color`, `is-arrayish`, `error-ex`, `simple-swizzle`, `has-ansi`, `ansi-regex`, `slice-ansi`.
- Versions malveillantes spécifiques (ex: `chalk@5.6.1`, `debug@4.4.2`). Liste exacte : GitHub Security Advisory `GHSA-...`.
- Vérif : `npm audit` + scan manuel des hashes.

### npm — 2024 et avant
- **lottie-player** (octobre 2024) — `@lottiefiles/lottie-player` compromis.
- **ua-parser-js** (2021) — versions `0.7.29`, `0.8.0`, `1.0.0` malveillantes.
- **node-ipc** (mars 2022) — protestware effaçant des fichiers.
- **event-stream** (2018) — historique mais à vérifier sur vieux projets.

### PyPI
- **xz-utils backdoor** (CVE-2024-3094, mars 2024) — versions `5.6.0` et `5.6.1` de `xz`/`liblzma`. Pas Python directement mais affecte tout container basé sur Debian/Ubuntu non patché.
- **ctx** et **phpass** (mai 2022) — paquets typosquattés.
- **colorama / fake colorama** — typosquatting récurrent.
- Vérif générique : `pip-audit` + `safety check` + `grep -r "atexit.register" site-packages | head` pour détecter des exfiltrations.

### Actions GitHub
- **tj-actions/changed-files** (mars 2025) — compromis, exfiltrait les secrets dans les logs publics.
- Vérif : `grep -r "tj-actions" .github/workflows/` et confirmer pinning par SHA, pas par tag.
- Plus généralement : tout `uses: <author>/<action>@v...` sans SHA est à reprendre.

### Tâches Spec correspondantes (à ajouter en Wave 1, en plus des génériques)

@@@task
# Scan Shai-Hulud + worms npm récents
## Scope
Vérifier que le lockfile n'inclut aucune version compromise par Shai-Hulud (sept 2025), s1ngularity/nx (août 2025), Qix/chalk (sept 2024), tj-actions, lottie-player, ua-parser-js, node-ipc.
## Definition of Done
- Liste exhaustive des paquets vérifiés (nom + version installée + statut: SAFE / COMPROMISED / UPGRADED).
- Tout paquet compromis : upgrade vers la version safe + audit des secrets potentiellement exfiltrés (rotation tokens npm/GitHub/cloud si compromission confirmée).
- Document dans le Spec section "Supply chain scan results".
## Verification
- `npm audit --audit-level=moderate` clean.
- `git log --all -p -- package-lock.json` n'introduit pas de versions blacklistées.
- Pour les actions GitHub : tous les `uses:` pointent vers un SHA 40-char.
@@@

@@@task
# Rotation tokens si compromission détectée
## Scope
Si la tâche précédente a trouvé un paquet compromis QUI A ÉTÉ INSTALLÉ (pas juste dans le lockfile mais exécuté en CI ou en local par un dev), rotation immédiate de :
- Tokens npm (CI + locaux des maintainers).
- Tokens GitHub (PAT, app tokens).
- Tokens cloud référencés dans les env du CI ou des shells des dev.
- Cookies de session des services intégrés.
## Definition of Done
- Liste des tokens identifiés à risque (par fichier/CI, sans révéler les valeurs).
- Confirmation utilisateur qu'ils ont été rotés (l'agent ne tourne PAS les tokens lui-même).
## Verification
- Documenter dans le Spec : `Token X : rotated YYYY-MM-DD`.
@@@

@@@task
# Audit postinstall scripts (toutes deps)
## Scope
Lister tous les scripts `preinstall`, `install`, `postinstall` dans `node_modules/*/package.json` et dans `package.json` racine + workspaces. Identifier ceux qui font des accès réseau ou lisent des fichiers sensibles (`~/.npmrc`, `~/.aws`, `~/.ssh`, `~/.config/gh`).
## Definition of Done
- Liste des scripts trouvés + verdict (legit / suspect / malveillant).
- Pour les suspects : analyse manuelle.
- Recommandation : activer `npm config set ignore-scripts true` côté CI si possible.
## Verification
- Pas d'exfiltration détectée dans les scripts.
@@@

**Notes complémentaires sur la Phase 1.5** :
- La liste évolue vite — l'implementor doit aussi consulter `https://github.com/advisories?query=type%3Areviewed+ecosystem%3Anpm` et `https://osv.dev/list` au moment où il bosse, plus les dernières 4 semaines de news GitHub Security Lab.
- Shai-Hulud est en premier vu que c'est l'attaque la plus récente et la plus virulente (auto-réplication).
- Le pattern « rotation tokens » est critique : si un dev a `npm install` sur une version compromise, ses creds sont potentiellement leakées même si on retire ensuite le paquet du lockfile.

## Phase 2 — Plan (Spec note, format `@@@task`)

Crée une **tâche par catégorie** dans le Spec. Ordre suggéré (par criticité) :

### Wave 1 — Supply chain (CRITIQUE)
- `@@@task` **Lockfile integrity audit** — vérifier qu'aucun lockfile n'a été modifié hors PR, comparer les hashes intégrité (`integrity` field dans `package-lock.json`).
- `@@@task` **Compromised packages scan** — chercher les paquets connus comme compromis récemment (consulter advisories npm/PyPI/etc., GitHub Security Advisories, `osv.dev`). Lister chaque hit. Voir aussi les 3 tâches dédiées de la Phase 1.5.
- `@@@task` **High/Critical CVEs** — `npm audit`, `pip-audit`, `cargo audit`, etc. Fixer ou justifier chaque high/critical.
- `@@@task` **Dependency pinning** — vérifier que les versions sont pinnées (pas de `^` ou `~` agressifs sur les deps sensibles : auth, crypto, http server). Pour les actions GitHub : pin par SHA, pas par tag.
- `@@@task` **Renovate/Dependabot config** — vérifier la config (groupage, auto-merge limité, fenêtres de release).

### Wave 2 — Auth & secrets
- `@@@task` **Audit secrets management** — aucun secret hardcodé, `.env` gitignored, `.env.example` à jour. Vérifier rotation possible.
- `@@@task` **JWT/session hardening** — si JWT : algo `RS256` ou `HS256` avec secret ≥ 256 bits, `exp` court, refresh tokens si applicable. Vérifier `iss`, `aud`, `nbf`. Cookies : `HttpOnly`, `Secure`, `SameSite=Lax` ou `Strict`.
- `@@@task` **OAuth/SSO callbacks** — valider `state`, `redirect_uri` whitelist exacte, PKCE pour SPAs publics.
- `@@@task` **Password storage** (si applicable) — bcrypt/argon2 avec coût adéquat, pas de MD5/SHA1.

### Wave 3 — Surface réseau & HTTP
- `@@@task` **CORS** — pas de `*` en prod, origines explicites par env, `credentials: true` uniquement si nécessaire.
- `@@@task` **Security headers** — `Strict-Transport-Security`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY` ou CSP `frame-ancestors`, `Referrer-Policy`, `Content-Security-Policy` adapté.
- `@@@task` **Rate limiting** — sur les endpoints d'auth et coûteux (export, search).
- `@@@task` **Input validation** — Pydantic/Zod/Joi sur toutes les entrées API, validation longueur/format.

### Wave 4 — Données & accès
- `@@@task` **SQL injection** — aucun query string concaténée, uniquement params bindés (`text(...)` avec params nommés en SQLAlchemy, prepared statements).
- `@@@task` **Authorization checks** — vérifier que chaque endpoint contrôle le rôle/scope (pas seulement l'authentification).
- `@@@task` **IDOR** — vérifier que les `id` dans les URLs sont validés contre l'utilisateur courant (ex: `/api/users/{id}` doit refuser un id qui n'appartient pas à l'utilisateur, sauf admin).
- `@@@task` **Logging** — pas de secrets/tokens/PII dans les logs ; logs d'audit pour les actions sensibles.

### Wave 5 — CI/CD & infra
- `@@@task` **GitHub Actions pinning** — `uses: actions/checkout@<sha>` partout, pas `@v4`.
- `@@@task` **Permissions workflows** — `permissions:` explicites, principe du moindre privilège (`contents: read` par défaut).
- `@@@task` **Branch protection** — review requise, status checks obligatoires, no force push sur `main`.
- `@@@task` **Container/Cloud Run** — image base minimale (`distroless`, `alpine`), non-root user, `readOnlyRootFilesystem` si possible. Secrets via Secret Manager, pas via env vars en clair dans les manifestes.

## Phase 3 — Exécution

Pour chaque wave :
1. STOP, présente le plan au user, attend l'approbation.
2. Délègue **toutes les tâches de la wave en parallèle** avec `waitMode: "after_all"` et `specialist: "implementor"`.
3. END TURN, attends la complétion de toute la wave.
4. Délègue un `specialist: "verifier"` qui relit les diffs et confirme.
5. Merge les PRs (une par tâche, ou une PR par wave si tu préfères — décide avec l'user).

## Règles strictes

- **Une PR par fix** (sauf petits fixes regroupés explicitement).
- **Tests** : chaque PR doit ajouter ou garder verts les tests existants.
- **Branches** : `security/<wave>-<short-desc>` (ex: `security/wave1-npm-audit`).
- **Cible** : `main` (ou `master` selon le repo).
- **Squash merge** par défaut.
- **Jamais de secret en clair** dans les commits, PR body, ou logs (même temporaires).
- **Prod en dernier** : si un changement touche prod (CORS, headers), feature-flag d'abord, prod en dernière étape.
- **Rollback plan** documenté dans chaque PR à risque (config infra, deps majeures).

## Livrables attendus

- Spec note maintenue à jour, une section par wave.
- Une PR par tâche complétée, mergée et déployée en dev/test avant prod.
- Rapport final : tableau `Catégorie | Avant | Après | Référence (CVE/PR)`.

## Démarrage

1. Renomme le workspace (3-5 mots, sentence case, ex: « Security audit projet X »).
2. Fais la phase Recon.
3. Écris le Spec avec toutes les tâches identifiées en `@@@task`.
4. Présente le plan, demande l'approbation.
5. Délègue Wave 1.

## Notes d'utilisation de ce prompt

- Remplace les 3 placeholders en haut (`<chemin>`, `<stack>`, `<environnements>`).
- Si tu sais déjà quels paquets sont compromis sur la stack ciblée, ajoute-les explicitement dans la Phase 1.5 avant de démarrer.
- Adapte les waves selon la stack : si pas d'OAuth, retire la sous-tâche correspondante.
- Tu peux retirer des waves entières si déjà faites sur ce projet.
