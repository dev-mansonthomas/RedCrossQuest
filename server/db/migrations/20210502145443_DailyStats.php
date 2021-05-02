<?php

use Phinx\Migration\AbstractMigration;

class DailyStats extends AbstractMigration
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

      $daily_stats_before_rcq = $this->table('daily_stats_before_rcq');

      $daily_stats_before_rcq
        ->addColumn('nb_benevole'   , 'integer'  , array('null' => true))
        ->addColumn('nb_benevole_1j', 'integer'  , array('null' => true))
        ->addColumn('nb_heure'      , 'integer'  , array('null' => true))

        ->update();

    }
}
