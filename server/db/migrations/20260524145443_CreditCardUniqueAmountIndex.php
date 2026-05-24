<?php

use Phinx\Migration\AbstractMigration;

class CreditCardUniqueAmountIndex extends AbstractMigration
{
    /**
     * Add a UNIQUE composite index on credit_card(tronc_queteur_id, amount).
     *
     * Defense-in-depth filet de securite contre la duplication de lignes credit_card
     * observee en 2026 (tronc_queteur_id=36070 entre autres). Les couches superieures
     * couvrent deja le cas nominal:
     *   - Frontend: dedup pass au chargement + protection anti-double-clic sur Sauvegarder
     *               + hasCBDetailsForDuplicateAmount() bloquant la soumission.
     *   - Backend : CreditCardDBService::dedupAndValidateCBEntities (pre-flight + log ERROR)
     *               + persistence en DELETE bulk + INSERT all dans une transaction.
     *
     * Cette contrainte garantit qu'aucun chemin (replay request, ecriture directe en base,
     * regression future) ne pourra reintroduire deux lignes avec le meme (tronc_queteur_id,
     * amount). Une violation provoque un erreur SQL 23000 (duplicate key) immediatement
     * rollback par la transaction enveloppante (cf. CreditCardDBService).
     *
     * Prerequis: les doublons existants en production doivent etre nettoyes AVANT que cette
     * migration soit jouee (deploy strategy: dev -> test -> prod, cleanup avant chaque
     * application). Sinon la creation de l'index echoue avec un ERROR 1062.
     */
    public function change()
    {
      $this->table('credit_card')
        ->addIndex(['tronc_queteur_id', 'amount'], [
          'unique' => true,
          'name'   => 'idx_credit_card_tronc_queteur_amount_unique',
        ])
        ->save();
    }
}
