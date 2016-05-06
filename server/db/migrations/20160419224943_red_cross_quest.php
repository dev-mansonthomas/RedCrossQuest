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
        $ul_table ->addColumn('name'        , 'string' , array('limit' => 50))
                  ->addColumn('phone'       , 'string' , array('limit' => 13))
                  ->addColumn('latitude'    , 'decimal', array('precision' => 10, 'scale' => 6))
                  ->addColumn('longitude'   , 'decimal', array('precision' => 10, 'scale' => 6))
                  ->addColumn('address'     , 'string' , array('limit' => 200))
                  ->addColumn('postal_code' , 'string' , array('limit' => 15))
                  ->addColumn('city'        , 'string' , array('limit' => 70))
                  ->addColumn('external_id' , 'integer')
                  ->create();

        $ul_table->insert([
          ["name" => "Non Bénévole", "phone" => "N/A", "longitude" => "0", "latitude" => "0", "address" => "N/A","postal_code" => "N/A"  ,"city" => "N/A"  , "external_id" => 0],
          ["name" => "Paris IV"    , "phone" => "N/A", "longitude" => "0", "latitude" => "0", "address" => "N/A","postal_code" => "75004","city" => "Paris", "external_id" => 3]
        ]);
        $ul_table->saveData();

        $queteur_table = $this->table('queteur');
        $queteur_table
          ->addColumn('first_name'   , 'string', array('limit' => 100))
          ->addColumn('last_name'    , 'string', array('limit' => 100))
          ->addColumn('minor'        , 'boolean')
          ->addColumn('email'        , 'string', array('limit' => 100))     //non unique : parents may leave there email for child
          ->addColumn('password'     , 'string', array('limit' => 50, 'null' => true))
          ->addColumn('password_salt', 'string', array('limit' => 50, 'null' => true))
          ->addColumn('secteur'      , 'integer')
          ->addColumn('nivol'        , 'string', array('limit' => 10))     //non unique : non volunteer won't have NIVOL, some volunteer may not know there NIVOL
          ->addColumn('mobile'       , 'string', array('limit' => 13))     //non unique : parents may leave there mobile for child
          ->addColumn('created'      , 'datetime')
          ->addColumn('updated'      , 'datetime', array('null' => true))

          ->addColumn('parent_authorization'    , 'blob', array('null' => true))
          ->addColumn('temporary_volunteer_form', 'blob', array('null' => true))

          ->addColumn('notes', 'text', array('null' => true))
          ->addColumn('ul_id', 'integer')

          ->addForeignKey('ul_id', 'ul', 'id')
          ->create();


      $tronc_table = $this->table('tronc');
      $tronc_table
        ->addColumn('ul_id', 'integer')
        ->addColumn('created'      , 'datetime')
        ->addColumn('enabled'      , 'boolean', array('default', 1))
        ->addColumn('notes', 'string', array('limit' => 255, 'null' => true))
        ->addForeignKey('ul_id', 'ul', 'id')
        ->create();


      $point_quete_table = $this->table('point_quete');
      $point_quete_table
        ->addColumn('ul_id'           , 'integer')
        ->addColumn('code'            , 'string' , array('limit' => 10))
        ->addColumn('name'            , 'string' , array('limit' => 30))
        ->addColumn('latitude'        , 'decimal', array('precision' => 10, 'scale' => 6))
        ->addColumn('longitude'       , 'decimal', array('precision' => 10, 'scale' => 6))
        ->addColumn('address'         , 'string' , array('limit' => 70))
        ->addColumn('postal_code'     , 'string' , array('limit' => 15))
        ->addColumn('city'            , 'string' , array('limit' => 70))
        ->addColumn('max_people'      , 'text'  )
        ->addColumn('advice'          , 'text'  , array('null' => true))
        ->addColumn('localization'    , 'text'  )
        ->addColumn('minor_allowed'   , 'boolean')
        ->addColumn('created'         , 'datetime')

        ->addForeignKey('ul_id', 'ul', 'id')

        ->create();





      $tronc_queteur_table = $this->table('tronc_queteur');
      $tronc_queteur_table
        ->addColumn('queteur_id'    , 'integer')
        ->addColumn('point_quete_id', 'integer')
        ->addColumn('tronc_id'      , 'integer')

        ->addColumn('depart_theorique', 'datetime')
        ->addColumn('depart'    , 'datetime', array('null' => true))
        ->addColumn('retour'    , 'datetime', array('null' => true))

        ->addColumn('euro500'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 500€'))
        ->addColumn('euro200'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 200€'))
        ->addColumn('euro100'   , 'integer', array('null' => true, 'comment' => 'nombre de billets de 100€'))
        ->addColumn('euro50'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  50€'))
        ->addColumn('euro20'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  20€'))
        ->addColumn('euro10'    , 'integer', array('null' => true, 'comment' => 'nombre de billets de  10€'))
        ->addColumn('euro5'     , 'integer', array('null' => true, 'comment' => 'nombre de billets de   5€'))
        ->addColumn('euro2'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de   2€'))
        ->addColumn('euro1'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de   1€'))
        ->addColumn('cents50'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     50 cents'))
        ->addColumn('cents20'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     20 cents'))
        ->addColumn('cents10'   , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de     10 cents'))
        ->addColumn('cents5'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      5 cents'))
        ->addColumn('cents2'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      2 cents'))
        ->addColumn('cent1'     , 'integer', array('null' => true, 'comment' => 'nombre de pièces  de      1 cent '))

        ->addColumn('foreign_coins'    , 'integer', array('null' => true, 'comment' => 'nombre de pièces  étrangères '))
        ->addColumn('foreign_banknote' , 'integer', array('null' => true, 'comment' => 'nombre de billets étranger'   ))

        ->addColumn('notes', 'text', array('null' => true))

        ->addForeignKey('queteur_id'    , 'queteur'    , 'id')
        ->addForeignKey('point_quete_id', 'point_quete', 'id')
        ->addForeignKey('tronc_id'      , 'tronc'      , 'id')

        ->create();

      $daily_stats_before_rcq = $this->table('daily_stats_before_rcq');
      $daily_stats_before_rcq
        ->addColumn('ul_id'           , 'integer', array('null' => false))
        ->addColumn('date'            , 'date'   , array('null' => false))
        ->addColumn('amount'          , 'decimal', array('null' => false, 'precision' => 10, 'scale' => 2))

        ->addForeignKey('ul_id', 'ul', 'id')
        ->create();

      $yearly_goal = $this->table('yearly_goal');
      $yearly_goal
        ->addColumn('ul_id'           , 'integer', array('null' => false))
        ->addColumn('year'            , 'integer', array('null' => false))
        ->addColumn('amount'          , 'decimal', array('null' => false, 'precision' => 10, 'scale' => 2))

        ->addForeignKey('ul_id', 'ul', 'id')
        ->create();


      $check_donation = $this->table('named_donation');
      $check_donation
        ->addColumn('ul_id'           , 'integer', array('null'  => false))
        ->addColumn('ref_recu_fiscal' , 'string' , array('null'  => false,'limit' => 100))
        ->addColumn('first_name'      , 'string' , array('null'  => false,'limit' => 100))
        ->addColumn('last_name'       , 'string' , array('null'  => false,'limit' => 100))
        ->addColumn('date'            , 'date'   , array('null'  => false))
        ->addColumn('amount'          , 'decimal', array('null'  => false, 'precision' => 10, 'scale' => 2))
        ->addColumn('address'         , 'string' , array('null'  => false,'limit' => 200))
        ->addColumn('postal_code'     , 'string' , array('null'  => false,'limit' => 15))
        ->addColumn('city'            , 'string' , array('null'  => false,'limit' => 70))
        ->addColumn('phone'           , 'string' , array('null'  => false,'limit' => 20))

        ->addColumn('donation_type'   , 'string' , array('null'  => false,'limit' => 70))
        ->addColumn('donation_nature' , 'string' , array('null'  => false,'limit' => 70))

        ->addForeignKey('ul_id', 'ul', 'id')
        ->create();
    }
}
