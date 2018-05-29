<?php

use Phinx\Migration\AbstractMigration;

class Queteur2 extends AbstractMigration
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

      $setToNullUpdateDate = "
update queteur set updated = null
where updated = '0000-00-00 00:00:00'";

      $this->execute($setToNullUpdateDate);

      $queteur = $this->table('queteur');

      $queteur
        ->addColumn    ('anonymization_user_id', 'integer' , array('null' => false, 'default' => 0 ))
        ->addForeignKey(    'anonymization_user_id', 'users', 'id')
        ->update();
    }
}
