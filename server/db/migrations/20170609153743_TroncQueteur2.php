<?php

use Phinx\Migration\AbstractMigration;

class TroncQueteur2 extends AbstractMigration
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
      $tq_table = $this->table('tronc_queteur');
      $tq_table
        ->addColumn   ('don_creditcard', 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))
        ->addColumn   ('don_cheque'    , 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))
        ->update();


      $named_donation = $this->table('named_donation');
      $named_donation
        ->addColumn   ('don_creditcard', 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2, 'default' => 0))
        ->renameColumn('cheque_amount'    , 'don_cheque')

        ->update();




    }
}
