# TODO

Liste des chantiers identifiés et différés. À implémenter quand l'occasion se présente.

---

## DB invariants — CHECK constraints sur les colonnes `nivol`

**Contexte.** Le NIVOL (identifiant Croix-Rouge volontaire) doit toujours être :
- en **majuscules** (`UPPER(nivol) = nivol`)
- **sans espaces** ni autre whitespace en début/fin (`TRIM(nivol) = nivol`)

Le code applicatif (PHP) repose aujourd'hui sur cet invariant sans le garantir au niveau base. Une analyse de prod (mai 2026) a révélé 7 lignes `queteur` avec un espace en fin de nivol, vestige d'un import en 2018. Données nettoyées via `UPDATE ... SET nivol = TRIM(nivol)`.

**Pourquoi pas un fix PHP ?** Les writes côté PHP n'ont pas produit de donnée sale depuis 2018, donc patcher chaque `insert`/`update` ajouterait du `strtoupper(trim(ltrim(...)))` partout sans bénéfice empirique. Une contrainte DB est plus chirurgicale.

**À implémenter** via une migration Phinx (`server/db/migrations/YYYYMMDDHHMMSS_NivolCheckConstraints.php`) :

```sql
ALTER TABLE queteur
  ADD CONSTRAINT chk_queteur_nivol_clean
  CHECK (nivol = UPPER(nivol) AND nivol = TRIM(nivol));

ALTER TABLE users
  ADD CONSTRAINT chk_users_nivol_clean
  CHECK (nivol = UPPER(nivol) AND nivol = TRIM(nivol));
```

**Pré-requis** avant migration :
1. Vérifier que les 3 envs (dev/test/prod) sont propres :
   ```sql
   SELECT COUNT(*) FROM queteur WHERE nivol != UPPER(nivol) OR LENGTH(nivol) != LENGTH(TRIM(nivol));
   SELECT COUNT(*) FROM users   WHERE nivol != UPPER(nivol) OR LENGTH(nivol) != LENGTH(TRIM(nivol));
   ```
2. Si lignes trouvées : `UPDATE ... SET nivol = UPPER(TRIM(nivol)) WHERE ...` avant l'ALTER.
3. Tester sur dev puis test avant prod.

**Compatibilité MariaDB.** Les CHECK constraints sont enforced depuis MariaDB 10.2.1 (2016). Vérifier la version cible (`SELECT VERSION()`).

**Comportement attendu après migration.** Toute tentative d'`INSERT`/`UPDATE` violant la contrainte échouera avec une `SQLSTATE[23000]` côté PHP. Le code applicatif `UserDBService::insert/updateNivol` et `QueteurDBService::insert/update` devra alors uppercaser/trimmer en amont — c'est précisément l'effet recherché (forcer la propreté à la source).

---

## PubSub cleanup — suppression du code mort autour de `tronc_queteur_*`

**Contexte.** L'infra PubSub des topics `tronc_queteur_*` alimentait une feature de synchro Google Spreadsheet temps-réel qui a été abandonnée. En dev, le topic `tronc_queteur_create` n'existe pas → l'API GCP renvoie `NOT_FOUND` et le serveur log `error while publishing PrepareTroncQueteur` à chaque préparation de tronc. Idem `tronc_queteur_update` côté `SaveCoins`/`SaveAsAdmin`.

**Action immédiate déjà appliquée.** Les 3 blocs `try { publish } catch { log }` ont été **commentés** (pas supprimés) dans :
- `server/src/routes/routesActions/troncsQueteurs/PrepareTroncQueteur.php` (topic `tronc_queteur_create`)
- `server/src/routes/routesActions/troncsQueteurs/SaveCoinsOnTroncQueteur.php` (topic `tronc_queteur_update`)
- `server/src/routes/routesActions/troncsQueteurs/SaveAsAdminOnTroncQueteur.php` (topic `tronc_queteur_update`)

→ Plus de logs d'erreur en dev. Aucun changement fonctionnel : les consommateurs étant absents, aucun pipeline aval ne dépendait de ces messages.

**Cleanup à faire** (à confirmer côté GCP que plus rien n'écoute avant de supprimer) :

1. **Vérifier les subscriptions GCP actives** sur chaque topic dans les 3 projets :
   ```bash
   for PROJECT in rcq-fr-dev rcq-fr-test rcq-fr-prod; do
     echo "=== $PROJECT ==="
     for TOPIC in tronc_queteur_create tronc_queteur_update tronc_queteur_depart tronc_queteur_return tronc_queteur_updateAsAdmin; do
       gcloud pubsub topics list-subscriptions $TOPIC --project=$PROJECT 2>&1 | grep -v "NOT_FOUND" | head -5
     done
   done
   ```
   Si une subscription est active en prod (BigQuery sink, Firestore sync RedQuest, autre Cloud Function), **stopper le cleanup** et garder le code.

2. **Supprimer définitivement** (si étape 1 confirme zero consumer) :
   - Les blocs commentés dans les 3 fichiers ci-dessus.
   - L'injection `PubSubService` (constructeur + propriété) dans les 3 controllers ; idem dans le container DI si plus aucun autre site n'en dépend.
   - Les `use` devenus inutiles : `PointQueteEntity`, `QueteurEntity`, `Logger`, `PubSubService` dans `PrepareTroncQueteur.php` ; `PubSubService`, `Logger` dans `SaveAsAdminOnTroncQueteur.php`.
   - La variable `$roleId` désormais non utilisée dans les 3 actions (signalé par l'IDE).
   - Les `getQueteurById` + `getPointQueteById` + construction `QueteurEntity` light / `PointQueteEntity` light dans `PrepareTroncQueteur` (n'existaient que pour enrichir le payload pubsub).
   - Les méthodes `preparePubSubPublishing()` / `genericPreparePubSubPublishing()` sur les entités `TroncQueteurEntity` / `QueteurEntity` / `PointQueteEntity` **si** plus aucun autre topic ne les utilise (à vérifier : `queteur_approval_topic` les utilise probablement encore).
   - Les clés de config dans `server/src/settings.php` : `PubSub.tronc_queteur_create_topic`, `PubSub.tronc_queteur_update_topic`.
   - Les topics dans `GCP/init_lib/create_topics.sh` : `tronc_queteur_create`, `tronc_queteur_update`, `tronc_queteur_depart`, `tronc_queteur_return`, `tronc_queteur_updateAsAdmin` (ces 3 derniers sont déjà sans producteur PHP, cf. `docs/pubsub_messages.md`).
   - Les topics GCP eux-mêmes via `gcloud pubsub topics delete` dans les 3 projets.
   - Mise à jour `docs/pubsub_messages.md` : retirer les sections `tronc_queteur_create` / `tronc_queteur_update` du tableau récap et marquer la rubrique 2.A/2.B comme retirée.

3. **Ne PAS toucher** au topic `queteur_approval_topic` qui a un consumer actif (`notifyRQOfRegistApproval` Cloud Function) → cf. `docs/pubsub_messages.md`.

**Risque du cleanup.** 🟡 Modéré : dépend entièrement de la vérification étape 1. Si un sink BigQuery historique consomme encore `tronc_queteur_create`/`update` en prod, le supprimer ferait perdre la collecte de ces événements. La version "commentée" actuelle est le compromis safe : zéro impact dev, zéro impact prod (la prod a probablement le topic existant donc le publish ne loggait rien d'anormal, mais le code commenté ne publie plus rien — à valider sur les dashboards BigQuery après déploiement).

---

## UX — bouton "Marquer comme supprimé" en tête de formulaire tronc_queteur

**Contexte.** Sur le formulaire de saisie d'un tronc_queteur (`client/src/app/troncs/troncQueteur/troncQueteur.html`), le bouton/contrôle "Marquer comme supprimé" se trouve actuellement en bas du formulaire. Quand un utilisateur veut juste supprimer un tronc_queteur, il doit scroller toute la page pour atteindre le contrôle.

**À faire.** Faire apparaître le bouton "Marquer comme supprimé" **en tête de formulaire** (en plus ou à la place de sa position actuelle, à arbitrer), pour permettre la suppression sans scroll.

**Pré-requis avant implémentation :**
- Vérifier les conditions d'affichage actuelles (rôle utilisateur, mode admin, état du tronc_queteur) pour les reproduire identiquement en tête.
- Garder la confirmation existante (radio button + dialog) pour éviter les suppressions accidentelles.

---
