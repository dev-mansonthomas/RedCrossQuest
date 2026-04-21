# PHPStan level 5 — findings restants

> Branche : `technical_upgrade`
> Dernier run : 2026-04-21 (après commit `774c4e5`)
> Total : **42 erreurs** (point de départ : 173).
> Configuration : `server/phpstan.neon` (level 5, include paths `src/`, `public/`).

## Rappel — commandes de vérification

```bash
# export JSON complet
docker compose exec -T -w /app/server php-fpm \
  vendor/bin/phpstan analyse --configuration=phpstan.neon \
  --memory-limit=1G --error-format=json --no-progress \
  > docs/audit/phpstan.json

# totals + ventilation par identifier
jq -r '.totals' docs/audit/phpstan.json
jq -r '.files | to_entries[] | .value.messages[] | .identifier' \
  docs/audit/phpstan.json | sort | uniq -c | sort -rn
```

## Rappel — tests E2E à exécuter AVANT le prochain merge

Le prochain lot de correctifs touche à des chemins critiques (login, écritures DB, exports). Rejouer manuellement les scénarios suivants **avant** de merger la branche `technical_upgrade` ou chaque PR de la série 5g→5j :

- [ ] **Login** — `POST /rest/authenticate` avec un utilisateur `dev`. Vérifier JWT retourné + claims (`username`, `roleId`, `ulId`, `d`, `queteurId`). Répéter le `roleId` dans l'URL pour valider le check de `AuthorisationMiddleware` (chemin 0006).
- [ ] **Reset password** — `POST /rest/sendInit` puis `POST /rest/resetPassword` (la logique a été nettoyée de l'ancienne couche ReCaptcha en PR 5f.1).
- [ ] **Save tronc quêteur (mode quêteur ET admin)** — `POST /rest/{roleId}/tronc_queteur/save*` : scénarios de PR 5g (bugs `$tq`/`$tqUpdated`/`$messageProperties` non définis sur chemin d'erreur).
- [ ] **Prepare tronc quêteur** — `POST /rest/{roleId}/tronc_queteur/prepare` : scénario rabbitmq (`$messageProperties`).
- [ ] **Export quêteur** — `GET /rest/{roleId}/queteurs/export*` et `GET /rest/{roleId}/exportData` : PR 5h (retour `int` vs `string` dans `ExportDataResponse`, mismatch sur statut mailing).
- [ ] **UL Registration** — soumission formulaire public puis validation admin : PR 5g (`$ulEntity`, `$lowerEnv` non définis).
- [ ] **Spotfire** — `GET /rest/{roleId}/spotfire/token` : `$validToken` non défini (PR 5g).

---

## Ventilation par identifier PHPStan

| Identifier | Count | Catégorie | PR cible |
|---|---|---|---|
| `variable.undefined` | 11 | Portée variable / bugs runtime | **5g** |
| `argument.type` | 7 | Mismatches `int`/`string`/logger | **5h** |
| `assign.propertyType` | 5 | Typage entités/responses | **5h** |
| `return.type` | 3 | Retour `int` vs `string` / `Auth` vs `Contract\Auth` | **5h**, **5i** |
| `property.onlyWritten` | 3 | Services injectés non lus (dead deps) | **5i** |
| `property.notFound` | 3 | Accès à props supprimées | **5i** |
| `arguments.count` | 2 | Appels avec trop de params | **5h** |
| `method.notFound` | 1 | `ResponseInterface::write()` (bug) | **5j** |
| `method.unused` | 1 | Méthode privée orpheline | **5i** |
| `instanceof.alwaysTrue` | 0 | — | — |
| `return.phpDocType` | 1 | PHPDoc désynchronisé | **5i** |
| `property.phpDocType` | 1 | PHPDoc désynchronisé | **5i** |
| `property.onlyRead` | 1 | Propriété `@Inject` jamais assignée | **5i** |
| `parameter.notFound` | 1 | `@param` PHPDoc orphelin | **5i** |
| `greater.invalid` | 1 | Comparaison `array > int` | **5h** |
| `encapsedStringPart.nonString` | 1 | Cast implicite d'objet dans `"..."` | **5j** |
| **Total** | **42** | | |

---

## PR 5g — Bugs de portée de variables (11 findings) 🟠

Ce sont des **vrais bugs runtime** : variables non définies sur certains chemins d'erreur. Si le chemin est emprunté, PHP émet `Undefined variable` en `E_WARNING` (sans break, mais avec pollution des logs et valeur `null` qui casse la suite).

### Fichiers concernés

| Fichier | Lignes | Variable | Cause supposée |
|---|---|---|---|
| `routes/routesActions/troncsQueteurs/SaveAsAdminOnTroncQueteur.php` | 76, 103, 104 | `$tq`, `$messageProperties`, `$tqUpdated` | `$tq` défini dans un `try` ; utilisé dans le code qui suit sans vérifier l'exception. |
| `routes/routesActions/troncsQueteurs/SaveCoinsOnTroncQueteur.php` | 115, 148, 149 | `$tq`, `$messageProperties`, `$tqUpdated` | idem |
| `routes/routesActions/troncsQueteurs/PrepareTroncQueteur.php` | 156 | `$messageProperties` | défini dans un `if` qui n'est pas toujours vrai |
| `routes/routesActions/ulRegistration/ValidateULRegistration.php` | 174, 221, 227 | `$ulEntity`, `$lowerEnv` | idem pattern `try`/`if` |
| `routes/routesActions/spotfire/GetSpotfireAccessToken.php` | 66 | `$validToken` | pas initialisé avant la branche de secours |

### Piste de correction

Pour chaque occurrence, initialiser la variable **avant le bloc try/if** (ex. `$tq = null;`, `$messageProperties = [];`) puis ajouter la garde `if ($tq === null) { return $errorResponse; }` sur le chemin d'erreur. **Ne pas** supprimer silencieusement : ce sont des chemins rarement testés mais utilisés lors des incidents (ex. RabbitMQ down).

### Test E2E associé

- Forcer un échec SQL sur `TroncQueteurDBService` (via transaction rollback) et vérifier que la réponse HTTP est bien `500` avec un log structuré, pas un PHP warning.
- Forcer une erreur RabbitMQ (service arrêté) et vérifier que `Prepare/Save TroncQueteur` renvoie `500` sans écraser la transaction DB.

---

## PR 5h — Mismatches de types int/string et payloads entités (14 findings) 🟠

Regroupe les `argument.type`, `assign.propertyType`, `arguments.count`, et le `return.type` sur `EmailBusinessService`.

### Sous-groupe 5h.1 — statuts mailing (4 findings)

- `BusinessService/EmailBusinessService.php:467, 507, 696, 700, 701` :
  - `sendExportDataQueteur()`/`sendExportDataUL()` déclarent retourner `string` mais `return` des `int`.
  - `insertQueteurMailingStatus($id, $status_code)` attend `string` ; passé `int`.
  - `MailingInfoEntity::$status` typé `int|null` ; assigné `string`.

**Piste** : uniformiser sur `int` partout (les statuts HTTP/SMTP sont des entiers). Corriger la signature de `MailingDBService::insertQueteurMailingStatus` (`int $status_code`), la colonne DB (`SMALLINT`), la PHPDoc des méthodes d'envoi, et l'entity.

### Sous-groupe 5h.2 — réponses Export (2 findings)

- `routes/routesActions/exportData/ExportData.php:65` et `routes/routesActions/queteurs/ExportQueteurData.php:74` : `new ExportDataResponse($status, …)` reçoit `string`, la classe attend `int`.

**Piste** : déclarer `$status` comme `int` en amont (vient du retour de `sendExportDataQueteur/UL` corrigé en 5h.1 — les deux sous-groupes sont liés).


### Sous-groupe 5h.3 — validators spec (3 findings)

- `Service/ClientInputValidatorSpecs.php:62, 70` : `$maxValue` typé `string`, assigné `int` ; `$defaultValue` typé `int|string|null`, assigné `bool|null`.
- `Service/ClientInputValidator.php:259, 261` : méthode `withInteger()` passe un `string` à `validateInteger(..., int $maxValue, ...)` et un `int|string|null` à `validateBoolean(..., ?bool $defaultValue)`.

**Piste** : revoir le design de `ClientInputValidatorSpecs`. Soit la classe accepte un type union correct (`int|string|null` pour `$maxValue`), soit il faut des factories séparées (`withInteger`, `withBoolean`) avec des DTO typés. Historiquement c'est une classe "tout-en-un" qui mérite un split. À attaquer avec précaution — elle est utilisée dans chaque `Action::action()`.

### Sous-groupe 5h.4 — settings/UL (3 findings)

- `BusinessService/SettingsBusinessService.php:87` : `GetULSetupStatusResponse::$BasePointQueteCreated` (typé `bool|null`) reçoit un `int` (résultat d'un `COUNT(*)`).
- `routes/routesActions/settings/UpdateULSettings.php:52` : appel `updateUL()` avec 3 paramètres pour une méthode qui en attend 2.
- `routes/routesActions/unitesLocales/ApproveULRegistration.php:137` : accès à `UniteLocaleEntity::$response` — propriété inexistante (copier-coller depuis une autre entité ?).

**Piste** : cast `(bool)` sur le résultat du COUNT ; vérifier la signature réelle de `updateUL` et corriger l'appel ; supprimer ou renommer l'accès `->response` (probablement `->registration_code_response` ou similaire — à confirmer par `git blame`).

### Sous-groupe 5h.5 — divers (2 findings)

- `DBService/TroncQueteurDBService.php:345` : `Carbon\Carbon::$tz` attend `CarbonTimeZone`, reçoit `string` → utiliser `$carbon->setTimezone('Europe/Paris')` au lieu de `$carbon->tz = 'Europe/Paris'`.
- `Service/Logger.php:308` : comparaison `array > 0` — probablement `count($arr) > 0` oublié.

### Test E2E associé

- Export données quêteur (envoi mail) : vérifier le code de retour SMTP stocké dans la table `queteur_mailing`.
- Update UL settings : éditer une UL depuis l'IHM admin et vérifier la persistance.

---

## PR 5i — Propriétés/méthodes orphelines et PHPDoc (9 findings) 🟢

Risque faible. Peut être fait en un seul commit.

| Fichier | Ligne | Problème | Action |
|---|---|---|---|
| `routes/routesActions/troncsQueteurs/TroncQueteurPreparationChecks.php` | 26 | `$troncQueteurBusinessService` injecté, jamais lu | Supprimer ctor param + prop si vraiment inutilisé, sinon utiliser (vérifier la logique métier). |
| `routes/routesActions/ulRegistration/CreateUniteLocaleInLowerEnv.php` | 34 | `$reCaptchaService` injecté, jamais lu | Supprimer (suite du cleanup ReCaptcha de PR 5f.1). |
| `routes/routesActions/ulRegistration/ValidateULRegistration.php` | 48 | `$redCallService` injecté, jamais lu | Vérifier si un appel RedCall manque (notification UL validée) ou supprimer. |
| `routes/routesActions/ulRegistration/RegisterNewUL.php` | 138 | `checkPresident()` privée, non appelée | Appeler depuis `action()` (cf. bug : le check président est désactivé !) **OU** supprimer. À trancher métier. |
| `routes/routesActions/settings/GetAllULSettings.php` | 67 | `$googleMapsApiKey` lu jamais écrit (manque l'`#[Inject]`) | Ajouter `#[Inject("googleMapsApiKey")]` sur la propriété (DI container l'a). |
| `routes/routesActions/troncsQueteurs/SaveAsAdminOnTroncQueteur.php` | 84 | `$tq->saveAsAdmin` | Propriété absente de `TroncQueteurEntity` → ajouter ou retirer. |
| `routes/routesActions/troncsQueteurs/SaveCoinsOnTroncQueteur.php` | 127 | idem | idem |
| `routes/routesActions/troncsQueteurs/PrepareTroncQueteurResponse.php` | 16 | PHPDoc `@var bool|null` vs native `bool` | Aligner : `private ?bool $troncInUse;`. |
| `DBService/UniteLocaleDBService.php` | 336 | `@param $query` et `@return array<>` désalignés (la méthode retourne `int`) | Nettoyer la PHPDoc. |
| `dependencies.php` | 418 | Factory typée `Kreait\Firebase\Auth` retourne `Contract\Auth` | Utiliser `Contract\Auth` en return type. |

---

## PR 5j — Vrais bugs isolés (2 findings) 🔴

### `routes/routesActions/queteurs/MarkAllQueteurQRCodeAsPrinted.php:50`

```php
$this->response->write(...);   // ResponseInterface n'a pas ::write()
```

Bug prod : doit être `$this->response->getBody()->write(...)`. Pattern identique dans tout le reste du projet. **Un simple patch 1 ligne**, à isoler dans son propre commit pour traçabilité.

### `Service/SecretManagerService.php:86`

```php
"... $secretId ..."   // $secretId est un objet AccessSecretVersionRequest
```

Dans le log `->warning("failed to get secret $secretId", …)`, `$secretId` est implicitement casté via `__toString()` qui n'existe pas sur l'objet proto. Pure ligne de log — remplacer par `->getName()` ou passer l'objet en contexte array.

---

## Hors scope PHPStan

- `Entity/Entity.php:44` et `Entity/CreditCardEntity.php:70` : ce sont des mismatches liés à la factory `ClientInputValidator` qui accepte `Logger` concret au lieu de `LoggerInterface`. À traiter en PR 5i **après** harmonisation des types dans `ClientInputValidator`.

---

## État d'avancement (série PR 5)

| PR | Commit | Findings résolus | Cumulé | État |
|---|---|---:|---:|---|
| 5a | `3ccdd59` | -5 | 173 → 168 | ✅ |
| 5b | `eb77083` | -4 | 168 → 164 | ✅ |
| 5c | `f945648` | div. PHPDoc | ≈ 164 | ✅ |
| 5e.1 | `3fa5071` | -94 | 173 → 79 | ✅ (base post-5c) |
| 5f.1 | `e0fb086` | -21 | 79 → 58 | ✅ |
| 5d | `774c4e5` | -16 | 58 → 42 | ✅ |
| **5g** | — | -11 attendu | 42 → 31 | ⏳ |
| **5h** | — | -14 attendu | 31 → 17 | ⏳ |
| **5i** | — | -9 attendu | 17 → ~5 | ⏳ |
| **5j** | — | -2 attendu | ~5 → ~3 | ⏳ |

Le résidu (~3) correspond aux findings structurels (ClientInputValidatorSpecs, Carbon tz) qui demandent un refacto plus long ou une décision produit.
