<?php

use Phinx\Migration\AbstractMigration;

class PointQuete2 extends AbstractMigration
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
      $queteur_table = $this->table('point_quete');
      $queteur_table
        ->addColumn('type'              , 'integer', array('default' => 1, 'precision' => 4, 'comment' => '1:Voix Publique, 2: Pietons, 3:Boutique, 4:Base UL'))
        ->addColumn('time_to_reach'     , 'integer', array('default' => 5, 'precision' => 4, 'comment' => 'Temps nécessaire pour atteindre le point de quete en minute'))
        ->addColumn('transport_to_reach', 'integer', array('default' => 1, 'precision' => 4, 'comment' => '1:à Pied, 2: Voiture, 3:Velo, 4:Train, 5:Autre'))
        ->update();

    }
}
