# Inventaire des messages PubSub publiés par le serveur RedCrossQuest

## 1. Vue d'ensemble — 3 topics actifs côté serveur PHP

Déclaration dans `server/src/settings.php` (clé `PubSub`) :

```php
'PubSub'      => [
  'tronc_queteur_update_topic' => 'tronc_queteur_update',
  'tronc_queteur_create_topic' => 'tronc_queteur_create',
  'queteur_approval_topic'     => 'queteur_approval_topic',
],
```

> Historique : `ul_update_topic` a été retiré localement (orphelin — aucun publisher
> côté PHP ni côté `rcq-functions-v2`). Le topic GCP `ul_update` reste provisionné
> comme point d'entrée legacy/manuel pour `ul-queteur-stats-per-year` (Gen2).
>
> ⚠️ **Action opérateur requise** : `server/src/settings.php` est gitignored et
> copié depuis `~/.cred/rcq-${COUNTRY}-${ENV}-settings.php` au déploiement
> (cf. `GCP/deploy_back.sh:95`). Pour propager le cleanup en `dev`/`test`/`prod`,
> supprimer la ligne `'ul_update_topic' => 'ul_update'` dans chacun des trois
> fichiers `~/.cred/rcq-fr-{dev,test,prod}-settings.php`.

Service générique : `server/src/Service/PubSubService.php::publish($topic, $data, $attributes, $jsonEncode=true, $raiseOnError=false)`.

Format publié = `{"data": json_encode($data), "attributes": {…}}`.

---

## 2. Détail par message

### A) `tronc_queteur_create`

| | |
|---|---|
| **Site** | `server/src/routes/routesActions/troncsQueteurs/PrepareTroncQueteur.php:146` |
| **Quand** | À la **préparation** d'un tronc-quêteur (un quêteur reçoit son tronc avant de partir). |
| **Payload (JSON)** | `TroncQueteurEntity` complet **après** `preparePubSubPublishing()` : dates Carbon converties en string (`Y-m-d H:i:s`), `tronc` et `rowCount` supprimés, et enrichi de `queteur` (`QueteurEntity` allégé, sans `referent_volunteerQueteur`/`user`) + `point_quete` (sans `max_people`). |
| **Attributes** | `ulId`, `uId` (user qui prépare), `queteurId`, `troncQueteurId` (tous en string) |
| **Finalité** | Alimenter BigQuery (analytique) **et** synchroniser Firestore pour que RedQuest (app mobile quêteur) voie l'affectation tronc↔quêteur↔point de quête en temps réel. |

### B) `tronc_queteur_update`

| | |
|---|---|
| **Sites** | `server/src/routes/routesActions/troncsQueteurs/SaveCoinsOnTroncQueteur.php:138` *et* `SaveAsAdminOnTroncQueteur.php:93` |
| **Quand** | Au comptage des espèces/billets/CB/chèques du tronc (mode normal ou mode admin). |
| **Payload (JSON)** | `TroncQueteurEntity` **complet rechargé depuis la base** via `getTroncQueteurById()` puis `preparePubSubPublishing()`. Contient tous les comptages (`euro500…euro001`, dons CB/chèques, `amount`, `weight`, `comptage`, `time_spent_in_hours`, `coins_money_bag_id`, `bills_money_bag_id`, etc.) + flag `saveAsAdmin=1` quand mode admin. |
| **Attributes** | `ulId`, `uId`, `queteurId`, `troncQueteurId` |
| **Finalité** | Pousser les montants définitifs vers **BigQuery** pour les stats UL/quêteur, et déclencher en aval `ComputeULStats` / `ULTriggerRecompute` qui recalculent les agrégats annuels. |

### C) `queteur_approval_topic`

| | |
|---|---|
| **Sites** | `server/src/routes/routesActions/queteurs/ApproveQueteurRegistration.php:111` *et* `AssociateRegistrationWithExistingQueteur.php:107` |
| **Quand** | Quand un responsable UL **valide** (ou associe à un quêteur existant) une demande d'inscription envoyée depuis RedQuest. |
| **Payload (JSON)** | `QueteurEntity` complet : `id`, `email`, `first_name`, `last_name`, `nivol`, `mobile`, `ul_id`/`ul_name`, `birthdate`, `man`, `active`, `registration_id`, `registration_approved`, `reject_reason`, `queteur_registration_token`, `firebase_uid`, `firebase_sign_in_provider`, etc. (voir `QueteurEntity::$_fieldList`). |
| **Attributes** | `ulId`, `uId` (validateur), `queteurId`, `registrationId` |
| **Finalité** | Déclenche la **Cloud Function `notifyRQOfRegistApproval`** (abonnée à ce topic — cf. `GCP/init_lib/common.sh:30`) qui notifie l'utilisateur RedQuest du verdict (approuvé/rejeté) et synchronise Firestore. |

### D) `ul_update` (topic GCP, plus exposé côté PHP)

| | |
|---|---|
| **Sites côté PHP** | aucun (retiré de `settings.php`, cleanup Wave 0). |
| **Producteur côté Gen2** | aucun non plus (`rcq-functions-v2/functions/**` : `0` appel à `publish()`). `ul-trigger-recompute` dispatche via **Cloud Tasks**, pas PubSub. |
| **Consommateur** | `ul-queteur-stats-per-year` (Gen2) abonné Eventarc → conservé comme point d'entrée manuel/legacy (`gcloud pubsub topics publish ul_update ...`). |
| **À noter** | Topic GCP encore provisionné dans `GCP/init_lib/create_topics.sh`. À supprimer en même temps qu'une éventuelle décommission de `ul-queteur-stats-per-year`. Pas de décision dans ce repo pour l'instant. |

---

## 3. Topics PubSub créés mais inutilisés ailleurs

`GCP/init_lib/create_topics.sh` provisionne 8 topics. Confrontation aux usages :

| Topic | Producteur PHP | Consommateur (CF) | Statut |
|---|---|---|---|
| `tronc_queteur_create` | ✅ PrepareTroncQueteur | ? (probablement BigQuery sink) | **actif** |
| `tronc_queteur_update` | ✅ SaveCoins + SaveAsAdmin | ? | **actif** |
| `tronc_queteur_depart` | ❌ aucun | ? | **mort** (les départs passent par `tronc_queteur_update` avec `depart` renseigné) |
| `tronc_queteur_return` | ❌ aucun | ? | **mort** (idem `retour`) |
| `tronc_queteur_updateAsAdmin` | ❌ aucun | ? | **mort** (flag `saveAsAdmin=1` dans `tronc_queteur_update`) |
| `queteur_approval_topic` | ✅ Approve + Associate | ✅ `notifyRQOfRegistApproval` | **actif** |
| `ul_update` | ❌ aucun (PHP ni Gen2) | `ul-queteur-stats-per-year` (Gen2) | legacy / point d'entrée manuel |
| `trigger_ul_update` | ❌ aucun (PHP) | ✅ `ULTriggerRecompute` | trigger CF→CF, hors scope serveur |

---

## 4. Récapitulatif tableau

| # | Topic | Émis par (PHP) | Payload | Attributes | Finalité |
|---|---|---|---|---|---|
| 1 | `tronc_queteur_create` | `PrepareTroncQueteur` | `TroncQueteurEntity` + `queteur` + `point_quete` (allégés, dates en string) | ulId, uId, queteurId, troncQueteurId | Synchro Firestore (RedQuest) + ingestion BigQuery |
| 2 | `tronc_queteur_update` | `SaveCoinsOnTroncQueteur`, `SaveAsAdminOnTroncQueteur` | `TroncQueteurEntity` complet rechargé + flag `saveAsAdmin` | idem | Comptages → BigQuery, déclenche recompute stats UL |
| 3 | `queteur_approval_topic` | `ApproveQueteurRegistration`, `AssociateRegistrationWithExistingQueteur` | `QueteurEntity` complet | ulId, uId, queteurId, registrationId | Trigger CF `notifyRQOfRegistApproval` → notif RedQuest + Firestore |


---

## 5. Notes de fiabilité

- 4 des 5 publishes utilisent `$raiseExceptionInCaseOfError=true` mais sont **enveloppés d'un `try/catch` qui swallow** l'exception (commentaire explicite `//do not rethrow`). Donc une panne PubSub **n'échoue pas** la requête HTTP — c'est intentionnel (le caissier ne doit pas être bloqué) mais ça veut dire qu'**aucune retry-queue** n'existe : un message perdu est perdu. À noter si on veut un jour fiabiliser (Outbox pattern, dead-letter, etc.).
- Avec PHP 8.5, `preparePubSubPublishing()` mute les `Carbon` en `string` — d'où les types unions (`Carbon|string|null`) sur les propriétés date de `TroncQueteurEntity`. Pas un risque, juste une dette de modèle (l'entité sert à la fois de DTO DB et de DTO PubSub).
