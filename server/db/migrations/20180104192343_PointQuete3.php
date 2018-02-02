<?php

use Phinx\Migration\AbstractMigration;

class PointQuete3 extends AbstractMigration
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

      $point_quete = $this->table('point_quete');
      $point_quete
        ->changeColumn('latitude'    , 'decimal', array('precision' => 18, 'scale' => 15))
        ->changeColumn('longitude'   , 'decimal', array('precision' => 18, 'scale' => 15))
        ->update();


      $ul = $this->table('ul');
      $ul
        ->changeColumn('latitude'    , 'decimal', array('precision' => 18, 'scale' => 15))
        ->changeColumn('longitude'   , 'decimal', array('precision' => 18, 'scale' => 15))
        ->update();









    }
}
