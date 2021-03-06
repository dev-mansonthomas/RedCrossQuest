<?php

use Phinx\Migration\AbstractMigration;

class QueteurAndPointQuete extends AbstractMigration
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
      $queteur_table = $this->table('queteur');
      $queteur_table
        ->removeColumn('password')
        ->removeColumn('password_salt')
        ->update();


      $point_quete_table = $this->table('point_quete');
      $point_quete_table
        ->changeColumn('latitude'    , 'decimal', array('precision' => 10, 'scale' => 8))
        ->changeColumn('longitude'   , 'decimal', array('precision' => 10, 'scale' => 8))
        ->update();


    }
}
