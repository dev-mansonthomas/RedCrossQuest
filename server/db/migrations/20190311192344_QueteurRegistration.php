<?php

use Phinx\Migration\AbstractMigration;

class QueteurRegistration extends AbstractMigration
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

      $queteur_table = $this->table('queteur_registration');
      $queteur_table
        ->addColumn('first_name'           , 'string' , array('limit' => 100  ))
        ->addColumn('last_name'            , 'string' , array('limit' => 100  ))
        ->addColumn('man'                  , 'boolean', array('null' => false, 'default' => 1))
        ->addColumn('birthdate'            , 'date'   , array('null'  => true ))
        ->addColumn('email'                , 'string' , array('limit' => 100  ))     //non unique : parents may leave there email for child
        ->addColumn('secteur'              , 'integer')
        ->addColumn('nivol'                , 'string' , array('limit' => 15, 'null' => true))     //non unique : non volunteer won't have NIVOL, some volunteer may not know their NIVOL
        ->addColumn('mobile'               , 'string' , array('limit' => 13   ))     //non unique : parents may leave there mobile for child
        ->addColumn('created'              , 'datetime')
        ->addColumn('ul_registration_token', 'string' , array('limit' => 36, 'null' => false))
        ->create();



    }
}
