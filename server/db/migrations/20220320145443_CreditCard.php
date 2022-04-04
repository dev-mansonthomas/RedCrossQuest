<?php

use Phinx\Migration\AbstractMigration;

class CreditCard extends AbstractMigration
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
      $credit_card_table = $this->table('credit_card');
      $credit_card_table
        ->addColumn('tronc_queteur_id', 'integer')
        ->addColumn('ul_id'           , 'integer')
        
        ->addColumn('quantity'        , 'integer')
        ->addColumn('amount'          , 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))
        
        ->addForeignKey('tronc_queteur_id', 'tronc_queteur', 'id')
        ->addForeignKey('ul_id'           , 'ul'           , 'id')
        ->create();
    }
}
