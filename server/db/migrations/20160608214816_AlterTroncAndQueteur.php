<?php

use Phinx\Migration\AbstractMigration;

class AlterTroncAndQueteur extends AbstractMigration
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

      $queteur_table = $this->table('queteur');
      $queteur_table
        ->addColumn('birthdate'         , 'date'   ,  array('null' => true                 ))
        ->addColumn('man'               , 'boolean',  array('null' => false, 'default' => 1))
        ->addColumn('active'            , 'boolean',  array('null' => false, 'default' => 1))
        ->addColumn('qr_code_printed'   , 'boolean',  array('null' => false, 'default' => 0))
        ->addColumn('referent_volunteer', 'integer',  array('null' => false, 'default' => 0))
        ->addForeignKey('referent_volunteer', 'queteur', 'id')
        ->update();



      $tronc_table = $this->table('tronc');
      $tronc_table
        ->addColumn('type', 'integer',  array('null' => false, 'default' => 1, 'comment' => '1:tronc, 2:urne chez commercant'))
        ->addColumn('qr_code_printed'   , 'boolean',  array('null' => false, 'default' => 0))
        ->update();

      $check_donation = $this->table('named_donation');
      $check_donation
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
        ->addColumn('notes'     , 'text'  )
        ->addColumn('type'      , 'integer',  array('null' => false, 'default' => 1, 'comment' => '1:cash, 2:cheque'))
        ->removeColumn('donation_nature')
        ->removeColumn('donation_type')
        ->renameColumn('amount', 'cheque_amount')
        ->update();


      $tronc_queteur_table = $this->table('tronc_queteur');
      $tronc_queteur_table
        ->renameColumn('notes'                       , 'notes_depart_theorique',  array('null' => true))
        ->addColumn   ('notes_depart'                , 'text',  array('null' => true))
        ->addColumn   ('notes_retour'                , 'text',  array('null' => true))
        ->addColumn   ('notes_retour_comptage_pieces', 'text',  array('null' => true))
        ->addColumn   ('notes_update'                , 'text',  array('null' => true))
        ->update();

    }
}
