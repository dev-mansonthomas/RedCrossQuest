<?php

use Phinx\Migration\AbstractMigration;

class RedCrossQuest extends AbstractMigration
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
        $ul_table ->addColumn('name'        , 'string')
                  ->addColumn('phone'       , 'string')
                  ->addColumn('longitude'   , 'string') //to send coordinates/addresse to people
                  ->addColumn('latitude'    , 'string')
                  ->addColumn('adresse'     , 'string')
                  ->addColumn('codePostale' , 'string')
                  ->addColumn('ville'       , 'string')
                  ->addColumn('pegass_id'   , 'integer')
                  ->create();

        $ul_table->insert([
          ["name" => "N/A", "pegass_id" => 0],
          ["name" => "Paris IV", "pegass_id" => 3]

        ]);
        $ul_table->saveData();

        $queteur_table = $this->table('queteur');
        $queteur_table
          ->addColumn('email'        , 'string', array('limit' => 100))     //non unique : parents may leave there email for child
          ->addColumn('password'     , 'string', array('limit' => 50))
          ->addColumn('password_salt', 'string', array('limit' => 50))
          ->addColumn('first_name'   , 'string', array('limit' => 50))
          ->addColumn('last_name'    , 'string', array('limit' => 50))
          ->addColumn('secteur'      , 'integer')
          ->addColumn('nivol'        , 'string', array('limit' => 10))     //non unique : non volunteer won't have NIVOL, some volunteer may not know there NIVOL
          ->addColumn('mobile'       , 'string', array('limit' => 13))     //non unique : parents may leave there mobile for child
          ->addColumn('created'      , 'datetime')
          ->addColumn('updated'      , 'datetime', array('null' => true))

          ->addColumn('parent_authorization'    , 'binary', array('null' => true))
          ->addColumn('temporary_volunteer_form', 'binary', array('null' => true))

          ->addColumn('notes', 'text')
          ->addColumn('ul_id', 'integer')
          ->addForeignKey('ul_id', 'ul', 'id')
          ->create();


      $tronc_table = $this->table('tronc');
      $tronc_table
        ->addColumn('ul_id', 'integer')
        ->addForeignKey('ul_id', 'ul', 'id')
        ->create();


      $point_quete_table = $this->table('point_quete');
      $point_quete_table
        ->addColumn('nom'         , 'string')
        ->addColumn('longitude'   , 'string')
        ->addColumn('latitude'    , 'string')
        ->addColumn('adresse'     , 'string')
        ->addColumn('codePostale' , 'string')
        ->addColumn('ville'       , 'string')
        ->addColumn('notes'       , 'text'  )
        ->create();

      $tronc_queteur_table = $this->table('tronc_queteur');
      $tronc_queteur_table
        ->addColumn('queteur_id'    , 'integer')
        ->addColumn('point_quete_id', 'integer')

        ->addColumn('depart'    , 'datetime')
        ->addColumn('retour'    , 'datetime')
        ->addColumn('euro500'   , 'integer', 'nombre de billets de 500€')
        ->addColumn('euro200'   , 'integer', 'nombre de billets de 200€')
        ->addColumn('euro100'   , 'integer', 'nombre de billets de 100€')
        ->addColumn('euro50'    , 'integer', 'nombre de billets de  50€')
        ->addColumn('euro20'    , 'integer', 'nombre de billets de  20€')
        ->addColumn('euro10'    , 'integer', 'nombre de billets de  10€')
        ->addColumn('euro5'     , 'integer', 'nombre de billets de   5€')
        ->addColumn('euro2'     , 'integer', 'nombre de pièces  de   2€')
        ->addColumn('euro1'     , 'integer', 'nombre de pièces  de   1€')
        ->addColumn('cents50'   , 'integer', 'nombre de pièces  de     50 cents')
        ->addColumn('cents20'   , 'integer', 'nombre de pièces  de     20 cents')
        ->addColumn('cents10'   , 'integer', 'nombre de pièces  de     10 cents')
        ->addColumn('cents5'    , 'integer', 'nombre de pièces  de      5 cents')
        ->addColumn('cents2'    , 'integer', 'nombre de pièces  de      2 cents')
        ->addColumn('cent1'     , 'integer', 'nombre de pièces  de      1 cent ')

        ->addForeignKey('queteur_id'    , 'queteur'    , 'id')
        ->addForeignKey('point_quete_id', 'point_quete', 'id')

        ->create();


    }
}
