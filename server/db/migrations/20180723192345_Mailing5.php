<?php

use Phinx\Migration\AbstractMigration;

class Mailing5 extends AbstractMigration
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
      $queteur = $this->table('queteur_mailing_status');

      $queteur
        ->addColumn('email_send_date'   , 'datetime', array('null' => false))
        ->addColumn('spotfire_open_date', 'datetime', array('null' => true ))
        ->update();
    }
}
