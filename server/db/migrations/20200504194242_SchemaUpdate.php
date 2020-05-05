<?php

use Phinx\Migration\AbstractMigration;

class SchemaUpdate extends AbstractMigration
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

      $troncQueteur = $this->table('tronc_queteur');

      //"alter table tronc_queteur_historique modify don_creditcard decimal(10,2) null;"

      $troncQueteur
        ->changeColumn('don_creditcard'     , 'float'  , array('null' => true))
        ->changeColumn('don_cheque'         , 'float'  , array('null' => true))
        ->changeColumn('don_cb_total_number', 'integer', array('null' => true))
        ->changeColumn('don_cheque_number'  , 'integer', array('null' => true))
        ->update();


      $troncQueteurHistorique = $this->table('tronc_queteur_historique');

      $troncQueteurHistorique
        ->changeColumn('don_creditcard'     , 'float'  , array('null' => true))
        ->changeColumn('don_cheque'         , 'float'  , array('null' => true))
        ->changeColumn('don_cb_total_number', 'integer', array('null' => true))
        ->changeColumn('don_cheque_number'  , 'integer', array('null' => true))
        ->update();


      $queteur = $this->table('queteur');
      $queteur
        ->changeColumn('email'     , 'string', array('limit' => 255))
        ->update();

      $named_donation = $this->table('named_donation');
      $named_donation
        ->changeColumn('email'     , 'string', array('limit' => 255))
        ->update();

    }
}
