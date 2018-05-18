<?php

use Phinx\Migration\AbstractMigration;

class NamedDonations extends AbstractMigration
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
      $tq_table = $this->table('named_donation');
      $tq_table
        ->addColumn   ('deleted'           ,'boolean', array('default'  => 0))
        ->addColumn   ('email'             ,'string' , array('limit'    => 100, 'after' => 'phone', 'null'  => true ))
        ->addColumn   ('forme'             ,'integer', array('default'  => 1  , 'after' => 'type' ))
        ->addColumn   ('coins_money_bag_id','string' , array('limit'    => 20 , 'null'  => true   ))
        ->addColumn   ('bills_money_bag_id','string' , array('limit'    => 20 , 'null'  => true   ))
        ->update();


    }
}
