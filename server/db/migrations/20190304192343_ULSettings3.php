<?php

use Phinx\Migration\AbstractMigration;

class ULSettings3 extends AbstractMigration
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
        ->addIndex(['ul_id'],
                   [
                     'unique' => true,
                     'name'   => 'idx_ul_settings_ul_id'
                   ])
        ->save();

    }
}
