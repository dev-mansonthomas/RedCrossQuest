<?php

use Phinx\Migration\AbstractMigration;

class UniteLocale extends AbstractMigration
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

      $ul_table = $this->table('ul');
      $ul_table
        ->addColumn('email', 'text',  array('null' => true,  'comment' => 'email de contact de l\'ul'))
        ->addColumn('id_structure_rattachement'   , 'integer',  array('null' => false, 'default' => 0, 'comment' => 'identification de la DT au dessus de l\'ul'))
        ->addColumn('date_demarrage_activite'   , 'datetime', array('null'    => true ,'comment' => 'date de création de l\'ul'))
        ->addColumn('date_demarrage_rcq'   , 'datetime', array('null'    => true, 'comment' => 'date début d\'utilisation de RCQ pour l\'ul'))
        ->update();


    }
}
