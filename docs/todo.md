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
