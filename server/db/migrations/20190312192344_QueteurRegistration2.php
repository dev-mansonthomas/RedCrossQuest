<?php

use Phinx\Migration\AbstractMigration;

class QueteurRegistration2 extends AbstractMigration
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
        ->addColumn('queteur_registration_token', 'string' , array('null' => false, 'limit'   => 36 ))
        ->addColumn('registration_approved'     , 'boolean', array('null' => true                   ))//null : not yet approved.
        ->addColumn('reject_reason'             , 'string' , array('null' => true , 'limit'   => 200))
        ->addColumn('queteur_id'                , 'integer', array('null' => false, 'default' => 0  ))
        ->addColumn('approver_user_id'          , 'integer', array('null' => false, 'default' => 0  ))
        ->addIndex(['queteur_registration_token'], [
          'unique' => true,
          'name' => 'idx_queteur_reg_token'])
        ->update();



    }
}
