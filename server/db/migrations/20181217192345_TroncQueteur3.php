<?php

use Phinx\Migration\AbstractMigration;

class TroncQueteur3 extends AbstractMigration
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

      $tronc_queteur_drop_trigger= "DROP TRIGGER IF EXISTS tronc_queteur_update";
      $this->execute($tronc_queteur_drop_trigger);


      $tronc_queteur_historique = $this->table('tronc_queteur_historique');


      $tronc_queteur_historique
        ->removeColumn ("don_cb_sans_contact_amount")
        ->removeColumn ("don_cb_sans_contact_number")
        ->addColumn    ('ul_id', 'integer', array('null' => true, 'after' => 'id'))
        ->addForeignKey('ul_id', 'ul', 'id')
        ->update();

      $tronc_queteur = $this->table('tronc_queteur');

      $tronc_queteur
        ->removeColumn ("don_cb_sans_contact_amount")
        ->removeColumn ("don_cb_sans_contact_number")
        ->addColumn    ('ul_id', 'integer', array('null' => true, 'after' => 'id'))
        ->addForeignKey('ul_id', 'ul', 'id')
        ->update();

      $update_tronc_queteur_historique = "update tronc_queteur_historique tq set ul_id = (select q.ul_id from queteur q where q.id = tq.queteur_id)";
      $update_tronc_queteur            = "update tronc_queteur tq            set ul_id = (select q.ul_id from queteur q where q.id = tq.queteur_id)";

      $this->execute($update_tronc_queteur_historique);
      $this->execute($update_tronc_queteur);

      $tronc_queteur_historique
        ->changeColumn("ul_id", 'integer', array('null' => false))
        ->update();

      $tronc_queteur
        ->changeColumn("ul_id", 'integer', array('null' => false))
        ->update();



      $tronc_queteur_trigger= "
CREATE TRIGGER tronc_queteur_update
AFTER UPDATE ON tronc_queteur
FOR EACH ROW
INSERT INTO tronc_queteur_historique
(    `insert_date`, `tronc_queteur_id`, `ul_id`,    `queteur_id`,     `point_quete_id`,     `tronc_id`,     `depart_theorique`,     `depart`,     `retour`,     `comptage`,
     `last_update`,     `last_update_user_id`,      `euro500`,     `euro200`,     `euro100`,     `euro50`,     `euro20`,     `euro10`,     `euro5`,     `euro2`,
     `euro1`,     `cents50`,     `cents20`,     `cents10`,     `cents5`,     `cents2`,     `cent1`,     `foreign_coins`,     `foreign_banknote`,
     `notes_depart_theorique`,     `notes_retour`,     `notes_retour_comptage_pieces`,     `notes_update`,     `deleted`,     `don_creditcard`, `don_cheque`,
     `coins_money_bag_id`,     `bills_money_bag_id`, `don_cb_total_number`, `don_cheque_number`)
VALUES
(NEW.last_update  , OLD.`id`, OLD.`ul_id`          , OLD.`queteur_id`, OLD.`point_quete_id`, OLD.`tronc_id`, OLD.`depart_theorique`, OLD.`depart`, OLD.`retour`, OLD.`comptage`,
 OLD.`last_update`, OLD.`last_update_user_id`,  OLD.`euro500`, OLD.`euro200`, OLD.`euro100`, OLD.`euro50`, OLD.`euro20`, OLD.`euro10`, OLD.`euro5`, OLD.`euro2`,
 OLD.`euro1`, OLD.`cents50`, OLD.`cents20`, OLD.`cents10`, OLD.`cents5`, OLD.`cents2`, OLD.`cent1`, OLD.`foreign_coins`, OLD.`foreign_banknote`,
 OLD.`notes_depart_theorique`, OLD.`notes_retour`, OLD.`notes_retour_comptage_pieces`, OLD.`notes_update`, OLD.`deleted`, OLD.`don_creditcard`, OLD.`don_cheque`,
 OLD.`coins_money_bag_id`, OLD.`bills_money_bag_id`, OLD.`don_cb_total_number`, OLD.`don_cheque_number`)
";


      $this->execute($tronc_queteur_trigger);


    }
}
