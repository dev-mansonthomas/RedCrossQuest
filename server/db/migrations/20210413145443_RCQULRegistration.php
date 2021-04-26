<?php

use Phinx\Migration\AbstractMigration;

class RCQULRegistration extends AbstractMigration
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

      $ul_registration = $this->table('ul_registration');

      $ul_registration
        ->addColumn('registration_token', 'string'  , array('null' => false , 'limit'   => 36))
        ->update();

      //remove leading space on cities
      $this->execute("update ul set city = trim(city)");

    }
}
