<?php

use Phinx\Migration\AbstractMigration;

class Mailing extends AbstractMigration
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

      $queteur
        ->addColumn('spotfire_access_token', 'string', array('limit' => 36, 'null' => true))
        ->update();


      $queteur_mailing_status = $this->table('queteur_mailing_status');
      $queteur_mailing_status
        ->addColumn('queteur_id' , 'integer')
        ->addColumn('year'       , 'integer')
        ->addColumn('status_code', 'string', array('limit' => 40))

        ->addForeignKey('queteur_id', 'queteur', 'id')
        ->create();


      $queteur = $this->table('ul');

      $queteur
        ->addColumn('spotfire_access_token', 'text', array('limit' => 36, 'null' => true))
        ->update();
    }
}
