<?php

use Phinx\Migration\AbstractMigration;

class UniteLocaleRegistration extends AbstractMigration
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

      $ul_table = $this->table('ul_registration');
      $ul_table
        ->addColumn('ul_id'                 , 'integer')
        ->addColumn('created'               , 'datetime', array('default' => 'CURRENT_TIMESTAMP'))
        ->addColumn('registration_approved' , 'boolean' , array('null' => true                   ))//null : not yet approved.
        ->addColumn('reject_reason'         , 'string'  , array('null' => true , 'limit'   => 200))
        ->addColumn('approval_date'         , 'datetime', array('null' => true))
        ->addColumn('president_man'         , 'boolean')
        ->addColumn('president_nivol'       , 'string', array('limit' => 15 ))
        ->addColumn('president_first_name'  , 'string', array('limit' => 100))
        ->addColumn('president_last_name'   , 'string', array('limit' => 100))
        ->addColumn('president_email'       , 'string', array('limit' => 100))
        ->addColumn('president_mobile'      , 'string', array('limit' => 20 ))

        ->addColumn('tresorier_man'         , 'boolean')
        ->addColumn('tresorier_nivol'       , 'string', array('limit' => 15 ))
        ->addColumn('tresorier_first_name'  , 'string', array('limit' => 100))
        ->addColumn('tresorier_last_name'   , 'string', array('limit' => 100))
        ->addColumn('tresorier_email'       , 'string', array('limit' => 100))
        ->addColumn('tresorier_mobile'      , 'string', array('limit' => 20 ))

        ->addColumn('admin_man'             , 'boolean')
        ->addColumn('admin_nivol'           , 'string', array('limit' => 15 ))
        ->addColumn('admin_first_name'      , 'string', array('limit' => 100))
        ->addColumn('admin_last_name'       , 'string', array('limit' => 100))
        ->addColumn('admin_email'           , 'string', array('limit' => 100))
        ->addColumn('admin_mobile'          , 'string', array('limit' => 20 ))
        ->addIndex (['ul_id'], ['unique' => true]) //only one registration per UL
        ->addForeignKey('ul_id', 'ul', 'id')
        
        ->save();
    }
}
