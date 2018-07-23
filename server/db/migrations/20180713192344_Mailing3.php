<?php

use Phinx\Migration\AbstractMigration;

class Mailing3 extends AbstractMigration
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
      $queteur = $this->table('ul_settings');

      $queteur
        ->addColumn('thanks_mail_benevole'  , 'string', array('limit' => 8000, 'null' => true))
        ->addColumn('thanks_mail_benevole1j', 'string', array('limit' => 8000, 'null' => true))
        ->update();
    }
}
