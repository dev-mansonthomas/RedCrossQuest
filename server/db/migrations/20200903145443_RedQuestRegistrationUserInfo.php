<?php

use Phinx\Migration\AbstractMigration;

class RedQuestRegistrationUserInfo extends AbstractMigration
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

      $queteur_registration = $this->table('queteur_registration');

      $queteur_registration
        ->addColumn('firebase_sign_in_provider', 'string', array('limit' => 100  ,'null' => true))
        ->addColumn('firebase_uid'             , 'string', array('limit' => 64   ,'null' => true))
        ->addColumn('benevole_referent'        , 'string', array('limit' => 100  ,'null' => true))
        ->changeColumn('email', 'string'  , array('null' => false , 'limit'   => 255))
        ->update();


      $queteur_table = $this->table('queteur');

      $queteur_table
        ->addColumn('firebase_sign_in_provider', 'string', array('limit' => 100  ,'null' => true))
        ->addColumn('firebase_uid'             , 'string', array('limit' => 64   ,'null' => true))
        ->update();


      $ul = $this->table('ul');

      $ul
        ->changeColumn('president_email', 'string'  , array('null' => true , 'limit'   => 255))
        ->changeColumn('tresorier_email', 'string'  , array('null' => true , 'limit'   => 255))
        ->changeColumn('admin_email'    , 'string'  , array('null' => true , 'limit'   => 255))

        ->update();

      $ul_registration = $this->table('ul_registration');

      $ul_registration
        ->changeColumn('president_email', 'string'  , array('null' => false , 'limit'   => 255))
        ->changeColumn('tresorier_email', 'string'  , array('null' => false , 'limit'   => 255))
        ->changeColumn('admin_email'    , 'string'  , array('null' => false , 'limit'   => 255))

        ->update();



    }
}
