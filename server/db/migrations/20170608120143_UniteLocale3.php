<?php

use Phinx\Migration\AbstractMigration;

class UniteLocale3 extends AbstractMigration
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
      $ul_table = $this->table('ul');
      $ul_table
        ->addColumn('mode'              , 'integer',  array(                'default' => 0                          , 'comment' => '0: non actif, 1: DailyStats, 2: Mode Province, 3: Mode Paris'))
        ->addColumn('publicDashboard'   , 'string' ,  array('limit' => 100, 'default' => 'RCQ-Public-MontantsCachés', 'comment' => 'Nom du dashboard spotfire public'))
        ->update();

    }
}
