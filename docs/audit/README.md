# Audit statique PHP 8.5 — Rapport (PR 1)

> Branche : `technical_upgrade`
> Cible runtime : **PHP 8.5** sur Google App Engine Standard (`php85`).
> Date : 2026-04-20.

## 1. Outillage installé

Ajouté dans `server/composer.json` (section `require-dev`) :

| Paquet | Version | Rôle |
|---|---|---|
| `phpstan/phpstan` | `^2.1` (installé 2.1.50) | Analyse statique |
| `phpstan/phpstan-deprecation-rules` | `^2.0` | Détection appels à code deprecated |
| `phpcompatibility/php-compatibility` | `^9.3` (9.3.5) | Audit compat cross-version PHP |
| `squizlabs/php_codesniffer` | `^3.11` | Runner nécessaire à PHPCompatibility |

### Scripts composer (exécutables depuis `server/`)

```bash
composer phpstan          # PHPStan niveau 5 sur src/ + public/ avec phpVersion=80500
composer phpcs:compat     # PHPCompatibility testVersion=8.5
composer audit:php85      # enchaîne les deux
```

Les hooks `post-install-cmd` / `post-update-cmd` reconfigurent automatiquement `installed_paths` de `phpcs` vers `vendor/phpcompatibility/php-compatibility` après un `composer install`.

## 2. Configurations

- **`server/phpstan.neon`** — level 5, `phpVersion: 80500`, scan de `src/` et `public/`, règles deprecation activées.
- **`server/phpcs.xml`** — standard `PHPCompatibility`, `testVersion=8.5`, scan de `src/` et `public/`.

## 3. Résultats bruts

| Fichier | Contenu |
|---|---|
| [`phpstan.txt`](./phpstan.txt) | Sortie complète PHPStan (1906 lignes, **396 erreurs**). |
| [`phpcompatibility.txt`](./phpcompatibility.txt) | Sortie complète PHPCompatibility (**1 erreur**). |
| [`manual_grep.txt`](./manual_grep.txt) | Grep manuel des patterns PHP 8.5 non couverts par l'outillage. |
| [`_manual_grep.sh`](./_manual_grep.sh) | Script reproductible du grep manuel. |

## 4. Synthèse des blocages PHP 8.5 / 8.4

### 4.1 PHPCompatibility (bloquant PHP 7.1+)

Une seule occurrence, **à corriger obligatoirement** :

| Fichier | Ligne | Règle | Message |
|---|---|---|---|
| `src/routes/routesActions/void/VoidAction.php` | 3 | `PHPCompatibility.Keywords.ForbiddenNamesAsDeclared.voidFound` | Namespace `RedCrossQuest\routes\routesActions\void` — `void` est un mot réservé depuis PHP 7.1. |

→ **Action PR 4** : renommer le répertoire `server/src/routes/routesActions/void/` (ex. `voidRoute/` ou `voidAction/`) et mettre à jour le namespace + `use` correspondants.

### 4.2 PHPStan — deprecations PHP 8.4 (deviendront fatales en PHP 9)

4 findings, tous du type `parameter.implicitlyNullable` (paramètre typé avec valeur par défaut `null` sans nullable explicite `?Type`) :

| Fichier | Ligne | Paramètre | Correction |
|---|---|---|---|
| `src/Exception/UserAlreadyExistsException.php` | 15 | `Throwable $previous` | `?Throwable $previous = null` |
| `src/Service/MailService.php` | 63 | `string $bcc` | `?string $bcc = null` |
| `src/Service/MailService.php` | 64 | `string $fileName` | `?string $fileName = null` |
| `src/Service/MailService.php` | 65 | `string $replyTo` | `?string $replyTo = null` |

→ **Action PR 4** : patch trivial (4 lignes), à inclure avec le renommage `void` pour grouper toutes les corrections compat dans une seule PR.

### 4.3 Grep manuel (PHP 8.5 specifics)

| Pattern | Résultat |
|---|---|
| Casts non-canoniques `(integer)` `(boolean)` `(double)` `(binary)` | ✅ aucun |
| `__sleep` / `__wakeup` (signature restrictions) | ✅ aucun |
| `DATE_RFC7231` / `DateTimeInterface::RFC7231` (deprecation) | ✅ aucun |
| Directive INI `disable_classes` | ✅ aucun |
| `get_defined_functions()` | ✅ aucun |
| Slim `setArgument` / `setArguments` (deprecated 4.15) | ✅ aucun |
| `null` comme array offset | ✅ aucun |
| Opérateur backtick shell (`` `cmd` ``) | ❌ faux positifs — uniquement quoting SQL MySQL dans `src/DBService/*`, aucun usage PHP. |

→ **Conclusion** : **aucun breaking change PHP 8.5 pur** dans la base de code.

## 5. Dette technique hors périmètre 8.5

Les **392 erreurs PHPStan restantes** (niveau 2) sont de la dette pré-existante sans lien avec la migration 8.5 :

- Variables non déclarées (`$logger` dans des contextes DI implicites).
- Retours de type divergents (`int` vs `string`).
- Propriétés non typées ou valeurs incompatibles (`MailingInfoEntity::$status`).
- Appels de méthodes inexistantes sur interfaces PSR (`LoggerInterface::setSlackService()`).

→ **Hors scope** de `technical_upgrade`. À traiter dans un chantier dédié "qualité".

## 6. Décision pour la suite

- ✅ **PR 1 (audit)** : livrable — configs + rapports + scripts. Cette PR.
- 🟢 **PR 2 (Slim 4.15 + PHP-DI 7.1 + Phinx 0.16.11)** : démarrable immédiatement, aucun blocker identifié par l'audit.
- 🟠 **PR 3 (Google SDKs + suppression hack `getLabel`)** : démarrable après PR 2.
- 🟠 **PR 4 (bump PHP 8.5 + compat)** : 1 renommage + 4 patches nullable. Très peu invasive.

## 7. Reproduire l'audit

Depuis le root du repo (stack Docker up) :

```bash
docker compose exec -T -w /app/server php-fpm composer audit:php85
bash docs/audit/_manual_grep.sh
```
