<?php

use Phinx\Migration\AbstractMigration;

class AddIndexes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     * https://book.cakephp.org/3.next/fr/phinx/migrations.html#working-with-indexes
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
      //https://dev.mysql.com/doc/refman/8.0/en/mysql-indexes.html
      //https://dev.mysql.com/doc/refman/8.0/en/multiple-column-indexes.html

      $this->table('daily_stats_before_rcq')
        ->addIndex(['date'])
        ->save();
      $this->table('named_donation')
        ->addIndex(['coins_money_bag_id'])
        ->addIndex(['bills_money_bag_id'])
        ->addIndex(['deleted'])
        ->addIndex(['donation_date'])
        ->addIndex(['first_name'])
        ->addIndex(['last_name'])
        ->addIndex(['email'])
        ->addIndex(['phone'])
        ->addIndex(['ref_recu_fiscal'])
        ->save();

      $this->table('point_quete')
        ->addIndex(['name'])
        ->addIndex(['code'])
        ->addIndex(['address'])
        ->addIndex(['city'])
        ->addIndex(['enabled'])
        ->addIndex(['type'])
        ->save();
      $this->table('queteur')
        ->addIndex(['anonymization_token', 'mailing_preference'])
        ->addIndex(['secteur'])
        ->addIndex(['first_name'])
        ->addIndex(['last_name'])
        ->addIndex(['email'])
        ->addIndex(['nivol'])
        ->addIndex(['qr_code_printed'])
        ->addIndex(['active'])
        ->addIndex(['spotfire_access_token'])
        ->save();
      $this->table('queteur_mailing_status')
        ->addIndex(['year', 'status_code'])
        ->save();
      $this->table('queteur_registration')
        ->addIndex(['ul_registration_token'])
        ->addIndex(['registration_approved'])
        ->addIndex(['created'])
        ->save();
      $this->table('spotfire_access')
        ->addIndex(['token'])
        ->addIndex(['token_expiration'])
        ->save();
      $this->table('tronc')
        ->addIndex(['type', 'enabled'])
        ->save();
      $this->table('tronc_queteur')
        ->addIndex(['deleted'])
        ->addIndex(['coins_money_bag_id'])
        ->addIndex(['bills_money_bag_id'])
        ->addIndex(['deleted'])
        ->addIndex(['depart_theorique'])
        ->addIndex(['depart'])
        ->addIndex(['retour'])
        ->addIndex(['comptage'])
        ->save();
      $this->table('users')
        ->addIndex(['active'])
        ->addIndex(['nivol'])
        ->addIndex(['init_passwd_uuid'])
        ->save();
      $this->table('yearly_goal')
        ->addIndex(['year'])
        ->save();
    }
}
