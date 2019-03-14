<?php

use Phinx\Migration\AbstractMigration;

class ULSettings2 extends AbstractMigration
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



      $tq_table = $this->table('ul_settings');
      $tq_table
        ->addColumn('token_benevole'   , 'string', array('limit' => 36, 'null' => true))
        ->addColumn('token_benevole_1j', 'string', array('limit' => 36, 'null' => true))
        ->update();
    }
}
