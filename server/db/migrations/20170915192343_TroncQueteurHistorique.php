<?php

use Phinx\Migration\AbstractMigration;

class TroncQueteurHistorique extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
      /*
       *
Executé manuellement.
      Besoin d'un user id=0 pour toutes les lignes existantes


INSERT INTO `users` (`nivol`, `queteur_id`, `password`, `role`, `created`, `updated`, `active`, `nb_of_failure`)
VALUES ('NA', '0', ' ', '0', '2017-09-16 23:45:07', '2017-09-16 23:45:07', '0', '0');

update `users` set id = 0 where id = 20;
       *
       * */





      $tq_table = $this->table('tronc_queteur');
      $tq_table
        ->addColumn   ('comptage'           , 'datetime', array('null' => true , 'after' => 'retour'      ))
        ->addColumn   ('last_update'        , 'datetime', array('null' => true , 'after' => 'comptage'    ))
        ->addColumn   ('last_update_user_id', 'integer' , array('null' => false, 'after' => 'last_update', 'default' => 0 ))
        ->removeColumn('notes_depart')
        ->update();




      $tronc_queteur_table = $this->table('tronc_queteur_historique');
      $tronc_queteur_table
        ->addColumn('insert_date'        , 'datetime', array('null' => true      ))
        ->addColumn('tronc_queteur_id'   , 'integer')
        ->addColumn('queteur_id'         , 'integer')
        ->addColumn('point_quete_id'     , 'integer')
        ->addColumn('tronc_id'           , 'integer')

        ->addColumn('depart_theorique'   , 'datetime')
        ->addColumn('depart'             , 'datetime', array('null' => true))
        ->addColumn('retour'             , 'datetime', array('null' => true))
        ->addColumn('comptage'           , 'datetime', array('null' => true , 'after' => 'retour'      ))
        ->addColumn('last_update'        , 'datetime', array('null' => true , 'after' => 'comptage'    ))
        ->addColumn('last_update_user_id', 'integer' , array('null' => false, 'after' => 'last_update' ))

        ->addColumn('euro500'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 500€'))
        ->addColumn('euro200'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 200€'))
        ->addColumn('euro100'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 100€'))
        ->addColumn('euro50'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  50€'))
        ->addColumn('euro20'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  20€'))
        ->addColumn('euro10'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  10€'))
        ->addColumn('euro5'     , 'integer', array('null' => true, 'comment' => 'nombre de billets de   5€'))
        ->addColumn('euro2'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de   2€'))
        ->addColumn('euro1'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de   1€'))
        ->addColumn('cents50'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     50 cents'))
        ->addColumn('cents20'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     20 cents'))
        ->addColumn('cents10'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     10 cents'))
        ->addColumn('cents5'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      5 cents'))
        ->addColumn('cents2'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      2 cents'))
        ->addColumn('cent1'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      1 cent '))

        ->addColumn('foreign_coins'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  étrangères '))
        ->addColumn('foreign_banknote' , 'integer', array('null' => true, 'comment' => 'nombre de billets étranger'   ))

        ->addColumn('notes_depart_theorique'      , 'text', array('null' => true))
        ->addColumn('notes_retour'                , 'text',  array('null' => true))
        ->addColumn('notes_retour_comptage_pieces', 'text',  array('null' => true))
        ->addColumn('notes_update'                , 'text',  array('null' => true))

        ->addColumn('deleted'             , 'boolean', array('default' => 0))
        ->addColumn('don_creditcard'      , 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))
        ->addColumn('don_cheque'          , 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))

        ->addForeignKey('queteur_id'         , 'queteur'       , 'id')
        ->addForeignKey('point_quete_id'     , 'point_quete'   , 'id')
        ->addForeignKey('tronc_id'           , 'tronc'         , 'id')
        ->addForeignKey('tronc_queteur_id'   , 'tronc_queteur' , 'id')
        ->addForeignKey('last_update_user_id', 'users'         , 'id')

        ->create();





    }
}
