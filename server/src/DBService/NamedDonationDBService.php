<?php /** @noinspection ALL */

namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\BillsMoneyBagSummaryEntity;
use RedCrossQuest\Entity\NamedDonationEntity;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\Entity\PageableResponseEntity;
use RedCrossQuest\Entity\PointQueteEntity;


class NamedDonationDBService extends DBService
{


  /**
   * Get all named donation for an UL
   *
   * @param PageableRequestEntity $pageableRequest
   *        string $query search string
   *        bool $deleted search for deleted or non deleted rows
   *        string $year search for named donation of the specified year, if null, search all year
   *        int $ulId the Id of the Unite Local
   * @return PageableResponseEntity list of NamedDonationEntity
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if parsing errors occurs
   * @noinspection SpellCheckingInspection
   */
  public function getNamedDonations(PageableRequestEntity $pageableRequest):PageableResponseEntity
  {
    //?string $query, bool $deleted, ?string $year, int $ulId):array
    /** @var string $query */
    $query            = $pageableRequest->filterMap['q'];
    /** @var string $year */
    $year             = $pageableRequest->filterMap['year'];

    $parameters      = ["ul_id" => $pageableRequest->filterMap['ul_id'], "deleted" =>$pageableRequest->filterMap['deleted']?1:0];

    //$this->logger->debug("searching named donation", $parameters);

    $searchSQL = "";
    $yearSQL   = "";
    if ($query !== null)
    {
      $searchSQL =  "
AND  
(       UPPER(`first_name`      ) like concat('%', UPPER(:query), '%')
  OR    UPPER(`last_name`       ) like concat('%', UPPER(:query), '%')
  OR    UPPER(`email`           ) like concat('%', UPPER(:query), '%')
  OR    UPPER(`phone`           ) like concat('%', UPPER(:query), '%')
  OR    UPPER(`ref_recu_fiscal` ) like concat('%', UPPER(:query), '%')
)
";
      $parameters['query'] = $query;
    }

    if($year !== null)
    {
      $yearSQL   = "AND YEAR(`donation_date`) = :year";
      $parameters['year'] = $year;
    }

    $sql = "
SELECT `id`,
       `ul_id`,
       `ref_recu_fiscal`,
       `first_name`,
       `last_name`,
       `donation_date`,
       `don_cheque`,
       `address`,
       `postal_code`,
       `city`,
       `phone`,
       `email`,
       `euro500`,
       `euro200`,
       `euro100`,
       `euro50`,
       `euro20`,
       `euro10`,
       `euro5`,
       `euro2`,
       `euro1`,
       `cents50`,
       `cents20`,
       `cents10`,
       `cents5`,
       `cents2`,
       `cent1`,
       `notes`,
       `type`,
       `forme`,
       `don_creditcard`,
       `deleted`,
       `coins_money_bag_id`,
       `bills_money_bag_id`,
       `last_update`,
       `last_update_user_id`
FROM `named_donation`
WHERE `ul_id`   = :ul_id
AND   `deleted` = :deleted
$searchSQL
$yearSQL
ORDER BY `id` DESC
";

    //$this->logger->info("search named_donation ", array('sql'=> $sql));
    $count   = $this->getCountForSQLQuery ($sql, $parameters);
    $results = $this->executeQueryForArray($sql, $parameters, function($row) {
      return new NamedDonationEntity($row, $this->logger);
    }, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);

    return new PageableResponseEntity($count, $results, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);
  }

  /**
   * Get One Named Donation
   *
   * @param int $id id of the named donation row
   * @param int $ulId the Id of the Unite Local
   * @param int $roleId the Id of the role of the user. if 9, do not check for ulId
   * @return NamedDonationEntity The NamedDonationEntity
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getNamedDonationById(int $id, int $ulId, int $roleId):NamedDonationEntity
  {
    $parameters = ["id"=> $id];

    $ulIdWhere = '';
    if($roleId != 9)
    {
      $parameters["ul_id"]= $ulId;
      $ulIdWhere = '
AND   `ul_id`   = :ul_id';
    }

    $sql = "
SELECT `id`,
       `ul_id`,
       `ref_recu_fiscal`,
       `first_name`,
       `last_name`,
       `donation_date`,
       `don_cheque`,
       `address`,
       `postal_code`,
       `city`,
       `phone`,
       `email`,
       `euro500`,
       `euro200`,
       `euro100`,
       `euro50`,
       `euro20`,
       `euro10`,
       `euro5`,
       `euro2`,
       `euro1`,
       `cents50`,
       `cents20`,
       `cents10`,
       `cents5`,
       `cents2`,
       `cent1`,
       `notes`,
       `type`,
       `forme`,
       `don_creditcard`,
       `deleted`,
       `coins_money_bag_id`,
       `bills_money_bag_id`,  
       `last_update_user_id`,
       `last_update`
FROM `named_donation`
WHERE `id`      = :id
$ulIdWhere
";

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters,function($row) {
      return new NamedDonationEntity($row, $this->logger);
    }, true);
  }

  /**
   * Insert one NamedDonation
   *
   * @param NamedDonationEntity $namedDonation         The namedDonation to be inserted
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @return int id of the new row
   * @throws PDOException if the query fails to execute on the server
   */
  public function insert(NamedDonationEntity $namedDonation, int $ulId, int $userId):int
  {

    $queryData = [
      'last_update_user_id'=> $userId,
      'ul_id'              => $ulId,
      'ref_recu_fiscal'    => $namedDonation->ref_recu_fiscal,
      'first_name'         => $namedDonation->first_name,
      'last_name'          => $namedDonation->last_name,
      'donation_date'      => $namedDonation->donation_date,
      'don_cheque'         => $namedDonation->don_cheque,
      'address'            => $namedDonation->address,
      'postal_code'        => $namedDonation->postal_code,
      'city'               => $namedDonation->city,
      'phone'              => $namedDonation->phone,
      'email'              => $namedDonation->email,
      'euro500'            => $namedDonation->euro500,
      'euro200'            => $namedDonation->euro200,
      'euro100'            => $namedDonation->euro100,
      'euro50'             => $namedDonation->euro50,
      'euro20'             => $namedDonation->euro20,
      'euro10'             => $namedDonation->euro10,
      'euro5'              => $namedDonation->euro5,
      'euro2'              => $namedDonation->euro2,
      'euro1'              => $namedDonation->euro1,
      'cents50'            => $namedDonation->cents50,
      'cents20'            => $namedDonation->cents20,
      'cents10'            => $namedDonation->cents10,
      'cents5'             => $namedDonation->cents5,
      'cents2'             => $namedDonation->cents2,
      'cent1'              => $namedDonation->cent1,
      'notes'              => $namedDonation->notes,
      'type'               => $namedDonation->type,
      'forme'              => $namedDonation->forme,
      'don_creditcard'     => $namedDonation->don_creditcard,
      'deleted'            => $namedDonation->deleted===true?"1":"0",
      'coins_money_bag_id' => $namedDonation->coins_money_bag_id,
      'bills_money_bag_id' => $namedDonation->bills_money_bag_id
    ];


      $sql = "
INSERT INTO `named_donation`
(
  `ul_id`,
  `ref_recu_fiscal`,
  `first_name`,
  `last_name`,
  `donation_date`,
  `don_cheque`,
  `address`,
  `postal_code`,
  `city`,
  `phone`,
  `email`,
  `euro500`,
  `euro200`,
  `euro100`,
  `euro50`,
  `euro20`,
  `euro10`,
  `euro5`,
  `euro2`,
  `euro1`,
  `cents50`,
  `cents20`,
  `cents10`,
  `cents5`,
  `cents2`,
  `cent1`,
  `notes`,
  `type`,
  `forme`,
  `don_creditcard`,
  `deleted`,
  `coins_money_bag_id`,
  `bills_money_bag_id`,
  `last_update_user_id`,
  `last_update`
)
VALUES
(
:ul_id,
:ref_recu_fiscal,
:first_name,
:last_name,
:donation_date,
:don_cheque,
:address,
:postal_code,
:city,
:phone,
:email,
:euro500,
:euro200,
:euro100,
:euro50,
:euro20,
:euro10,
:euro5,
:euro2,
:euro1,
:cents50,
:cents20,
:cents10,
:cents5,
:cents2,
:cent1,
:notes,
:type,
:forme,
:don_creditcard,
:deleted,
:coins_money_bag_id,
:bills_money_bag_id,
:last_update_user_id,
NOW()
);
";
    return $this->executeQueryForInsert($sql, $queryData, true);
  }


  /**
   * Update one NamedDonation
   *
   * @param NamedDonationEntity $namedDonation         The namedDonation to be inserted
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws PDOException if the query fails to execute on the server
   */
  public function update(NamedDonationEntity $namedDonation, int $ulId, int $userId):void
  {

    $queryData = [
      'last_update_user_id'=> $userId,
      'ul_id'              => $ulId,
      'id'                 => $namedDonation->id,
      'ref_recu_fiscal'    => $namedDonation->ref_recu_fiscal,
      'first_name'         => $namedDonation->first_name,
      'last_name'          => $namedDonation->last_name,
      'donation_date'      => $namedDonation->donation_date,
      'don_cheque'         => $namedDonation->don_cheque,
      'address'            => $namedDonation->address,
      'postal_code'        => $namedDonation->postal_code,
      'city'               => $namedDonation->city,
      'phone'              => $namedDonation->phone,
      'email'              => $namedDonation->email,
      'euro500'            => $namedDonation->euro500,
      'euro200'            => $namedDonation->euro200,
      'euro100'            => $namedDonation->euro100,
      'euro50'             => $namedDonation->euro50,
      'euro20'             => $namedDonation->euro20,
      'euro10'             => $namedDonation->euro10,
      'euro5'              => $namedDonation->euro5,
      'euro2'              => $namedDonation->euro2,
      'euro1'              => $namedDonation->euro1,
      'cents50'            => $namedDonation->cents50,
      'cents20'            => $namedDonation->cents20,
      'cents10'            => $namedDonation->cents10,
      'cents5'             => $namedDonation->cents5,
      'cents2'             => $namedDonation->cents2,
      'cent1'              => $namedDonation->cent1,
      'notes'              => $namedDonation->notes,
      'type'               => $namedDonation->type,
      'forme'              => $namedDonation->forme,
      'don_creditcard'     => $namedDonation->don_creditcard,
      'deleted'            => $namedDonation->deleted===true?"1":"0",
      'coins_money_bag_id' => $namedDonation->coins_money_bag_id,
      'bills_money_bag_id' => $namedDonation->bills_money_bag_id
    ];


    $sql = "
UPDATE `named_donation`
SET 
              
  `ref_recu_fiscal`     = :ref_recu_fiscal,     
  `first_name`          = :first_name,          
  `last_name`           = :last_name,           
  `donation_date`       = :donation_date,                
  `don_cheque`          = :don_cheque,          
  `address`             = :address,             
  `postal_code`         = :postal_code,         
  `city`                = :city,                
  `phone`               = :phone,               
  `email`               = :email,               
  `euro500`             = :euro500,             
  `euro200`             = :euro200,             
  `euro100`             = :euro100,             
  `euro50`              = :euro50,              
  `euro20`              = :euro20,              
  `euro10`              = :euro10,              
  `euro5`               = :euro5,               
  `euro2`               = :euro2,               
  `euro1`               = :euro1,               
  `cents50`             = :cents50,             
  `cents20`             = :cents20,             
  `cents10`             = :cents10,             
  `cents5`              = :cents5,              
  `cents2`              = :cents2,              
  `cent1`               = :cent1,               
  `notes`               = :notes,               
  `type`                = :type,                
  `forme`               = :forme,               
  `don_creditcard`      = :don_creditcard,      
  `deleted`             = :deleted,             
  `coins_money_bag_id`  = :coins_money_bag_id,  
  `bills_money_bag_id`  = :bills_money_bag_id,  
  `last_update_user_id` = :last_update_user_id, 
  `last_update`         = NOW()                 
WHERE `id`    = :id
AND   `ul_id` = :ul_id  
";

    $this->executeQueryForUpdate($sql, $queryData);
  }
}
