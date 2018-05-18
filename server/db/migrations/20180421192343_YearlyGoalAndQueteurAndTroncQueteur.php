<?php

use Phinx\Migration\AbstractMigration;

class YearlyGoalAndQueteurAndTroncQueteur extends AbstractMigration
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


      $yearly_goal = $this->table('yearly_goal');
      $yearly_goal
        ->addColumn('day_1_percentage' , 'integer')
        ->addColumn('day_2_percentage' , 'integer')
        ->addColumn('day_3_percentage' , 'integer')
        ->addColumn('day_4_percentage' , 'integer')
        ->addColumn('day_5_percentage' , 'integer')
        ->addColumn('day_6_percentage' , 'integer')
        ->addColumn('day_7_percentage' , 'integer')
        ->addColumn('day_8_percentage' , 'integer')
        ->addColumn('day_9_percentage' , 'integer')
        ->update();



      $queteur_table = $this->table('queteur');
      $queteur_table
        ->addColumn('anonymization_token','string'   , array('limit' => 36, 'null'  => true))
        ->addColumn('anonymization_date' ,'datetime' , array('limit' => 36, 'null'  => true))
        ->update();



      $tq_table = $this->table('tronc_queteur');
      $tq_table
        ->addColumn   ('coins_money_bag_id','string' , array('limit' => 20, 'null'  => true))
        ->addColumn   ('bills_money_bag_id','string' , array('limit' => 20, 'null'  => true))
        ->update();

    }
}
