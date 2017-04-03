<?php

use Phinx\Migration\AbstractMigration;

class Authentication extends AbstractMigration
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
      $users_table = $this->table('users');
      $users_table->addColumn('nivol'     , 'string' , array('limit' => 10))
        ->addColumn('queteur_id', 'integer')
        ->addColumn('password'  , 'string' , array('limit' => 60))

        ->addColumn('role'      , 'string' , array('limit' => 20))


        ->addColumn('created'      , 'datetime')
        ->addColumn('updated'      , 'datetime', array('null' => true))

        ->addColumn('active'                    , 'boolean' , array('default' => true ))
        ->addColumn('last_failure_login_date'   , 'datetime', array('null'    => true ))
        ->addColumn('nb_of_failure'             , 'integer' , array('default' => 0    ))
        ->addColumn('last_successful_login_date', 'datetime', array('null'    => true ))


        ->addForeignKey('queteur_id' , 'queteur', 'id')
        ->create();

       $users_table->insert(
         [
           [ 'nivol'                    =>   '75233A',
             'queteur_id'               =>   120,
             'password'                 =>   password_hash('test', PASSWORD_DEFAULT),
             'role'                     =>   '9',
             'created'                  =>   '2017-03-09 23:45:07',
             'updated'                  =>   '2017-03-09 23:45:07',
             'active'                   =>   1,
             'last_failure_login_date'  =>   '2017-03-09 23:45:07',
             'nb_of_failure'            =>   0,
             'last_successful_login_date'=>   '2017-03-09 23:45:07'
           ],[ 'nivol'                    =>   '0001T',
             'queteur_id'               =>   0,
             'password'                 =>   password_hash('test', PASSWORD_DEFAULT),
             'role'                     =>   '1',
             'created'                  =>   '2017-03-09 23:45:07',
             'updated'                  =>   '2017-03-09 23:45:07',
             'active'                   =>   1,
             'last_failure_login_date'  =>   '2017-03-09 23:45:07',
             'nb_of_failure'            =>   0,
             'last_successful_login_date'=>   '2017-03-09 23:45:07'
           ]
         ]
       )
         ->saveData();

    }
}
