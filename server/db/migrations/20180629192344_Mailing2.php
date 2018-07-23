<?php

use Phinx\Migration\AbstractMigration;

class Mailing2 extends AbstractMigration
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
      $queteur = $this->table('queteur');

      // 1: all communications, 0: None
      $queteur
        ->addColumn('mailing_preference', 'integer', array('default' => 1, 'null' => false))
        ->update();


      $queteur_mailing_status = $this->table('queteur_mailing_status');
      $queteur_mailing_status
        ->addIndex(['queteur_id', 'year'], ['unique' => true, 'name' => 'idx_queteur_year'])
        ->update();

    }
}
