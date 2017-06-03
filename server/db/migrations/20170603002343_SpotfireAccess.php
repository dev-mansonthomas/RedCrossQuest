<?php

use Phinx\Migration\AbstractMigration;

class SpotfireAccess extends AbstractMigration
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
      $spotfire_access_table = $this->table('spotfire_access');
      $spotfire_access_table
        ->addColumn('token'           , 'string', array('limit' => 36))
        ->addColumn('token_expiration', 'datetime')
        ->addColumn('ul_id'           , 'integer')
        ->addColumn('user_id'         , 'integer')

        ->addForeignKey('ul_id'  , 'ul'   , 'id')
        ->addForeignKey('user_id', 'users', 'id')
        ->create();
    }
}
