<?php
namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\BillsMoneyBagSummaryEntity;
use RedCrossQuest\Entity\CoinsMoneyBagSummaryEntity;


class MoneyBagDBService extends DBService
{
  /**
   * Search existing money_bag id from the current year and the current UniteLocale
   *
   * @param string $query the searched string
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param string $type "bill" or "coin" depending the kind of bag the user search
   * @return string[]  list of money_bag_id that match the search
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function searchMoneyBagId(string $query, string $type, int $ulId):array
  {
    $column = "coins_money_bag_id";
    if($type == "bill")
    {
      $column = "bills_money_bag_id";
    }

    $sql = "
SELECT DISTINCT t.money_bag_id FROM
(
  SELECT DISTINCT(tq.$column) as money_bag_id
  FROM   tronc_queteur tq
  WHERE  tq.$column   like :query
  AND YEAR(tq.depart) =    YEAR(NOW())
  AND tq.ul_id        =    :ul_id
  UNION
  SELECT DISTINCT(nd.$column) as money_bag_id
  FROM   named_donation nd
  WHERE  nd.$column          like :query
  AND YEAR(nd.donation_date) =    YEAR(NOW())
  AND nd.ul_id               =    :ul_id
) as t
ORDER BY t.money_bag_id DESC
";

  $parameters = ["query" => "%".$query."%", "ul_id" => $ulId];

  return $this->executeQueryForArray($sql, $parameters, function($row) {
    return $row['money_bag_id'];
  });
  }

  /**
   * Get the details of coins money bag : total amount, weight, per coins count & total
   *
   * @param int $ulId the id of the unite locale
   * @param string $coinsMoneyBagId The id of the coins bag
   * @return CoinsMoneyBagSummaryEntity  The tronc
   * @throws Exception if some parsing fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function getCoinsMoneyBagDetails(int $ulId, string $coinsMoneyBagId):?CoinsMoneyBagSummaryEntity
  {
    $parameters = [
      "ul_id"              => $ulId,
      "coins_money_bag_id" => $coinsMoneyBagId,
      "ul_id2"             => $ulId,
      "coins_money_bag_id2"=> $coinsMoneyBagId,
    ];


    $sql = "
SELECT coins_money_bag_id,
    SUM(
        euro2   * 2     +
        euro1   * 1     +
        cents50 * 0.5   +
        cents20 * 0.2   +
        cents10 * 0.1   +
        cents5  * 0.05  +
        cents2  * 0.02  +
        cent1   * 0.01
    ) as amount,
SUM(euro2    *2   ) as  total_euro2  ,
SUM(euro1    *1   ) as  total_euro1  ,
SUM(cents50  *0.5 ) as  total_cents50,
SUM(cents20  *0.2 ) as  total_cents20,
SUM(cents10  *0.1 ) as  total_cents10,
SUM(cents5   *0.05) as  total_cents5 ,
SUM(cents2   *0.02) as  total_cents2 ,
SUM(cent1    *0.01) as  total_cent1  ,
SUM(euro2  ) as  count_euro2         ,
SUM(euro1  ) as  count_euro1         ,
SUM(cents50) as  count_cents50       ,
SUM(cents20) as  count_cents20       ,
SUM(cents10) as  count_cents10       ,
SUM(cents5 ) as  count_cents5        ,
SUM(cents2 ) as  count_cents2        ,
SUM(cent1  ) as  count_cent1         ,
    SUM(
     euro2    * 8.5  +
     euro1    * 7.5  +
     cents50  * 7.8  +
     cents20  * 5.74 +
     cents10  * 4.1  +
     cents5   * 3.92 +
     cents2   * 3.06 +
     cent1    * 2.3
    ) as weight
from (
select
tq.coins_money_bag_id,
tq.euro2  ,
tq.euro1  ,
tq.cents50,
tq.cents20,
tq.cents10,
tq.cents5 ,
tq.cents2 ,
tq.cent1
  FROM tronc_queteur as tq
  WHERE tq.ul_id              = :ul_id
  AND   tq.coins_money_bag_id = :coins_money_bag_id
  AND   tq.deleted            = 0
UNION
select
nd.coins_money_bag_id,
nd.euro2  ,
nd.euro1  ,
nd.cents50,
nd.cents20,
nd.cents10,
nd.cents5 ,
nd.cents2 ,
nd.cent1
  FROM named_donation as nd
  WHERE nd.ul_id              = :ul_id2
  AND   nd.coins_money_bag_id = :coins_money_bag_id2
  AND   nd.deleted            = 0) as union_select ;
";

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters,function($row) {
      return new CoinsMoneyBagSummaryEntity($row, $this->logger);
    });
  }

  /**
   * Get the details of coins money bag : total amount, weight, per coins count & total
   *
   * @param int $ulId the id of the unite locale
   * @param string $billsMoneyBagId The id of the bills bag
   * @return BillsMoneyBagSummaryEntity  The tronc
   * @throws Exception if some parsing fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function getBillsMoneyBagDetails(int $ulId, string $billsMoneyBagId):?BillsMoneyBagSummaryEntity
  {
    $parameters = [
      "ul_id"              => $ulId,
      "bills_money_bag_id" => $billsMoneyBagId,
      "ul_id2"             => $ulId,
      "bills_money_bag_id2"=> $billsMoneyBagId,
    ];

    $sql = "
SELECT bills_money_bag_id,
       SUM(
        euro5    *5   +
        euro10   *10  +
        euro20   *20  +
        euro50   *50  +
        euro100  *100 +
        euro200  *200 +
        euro500  *500
    ) as amount,
SUM(euro5    *5   ) as  total_euro5  ,
SUM(euro10   *10  ) as  total_euro10 ,
SUM(euro20   *20  ) as  total_euro20 ,
SUM(euro50   *50  ) as  total_euro50 ,
SUM(euro100  *100 ) as  total_euro100,
SUM(euro200  *200 ) as  total_euro200,
SUM(euro500  *500 ) as  total_euro500,

SUM(euro5  ) as  count_euro5         ,
SUM(euro10 ) as  count_euro10        ,
SUM(euro20 ) as  count_euro20        ,
SUM(euro50 ) as  count_euro50        ,
SUM(euro100) as  count_euro100       ,
SUM(euro200) as  count_euro200       ,
SUM(euro500) as  count_euro500       ,

    SUM(
     euro500  * 1.1  +
     euro200  * 1.1  +
     euro100  * 1    +
     euro50   * 0.9  +
     euro20   * 0.8  +
     euro10   * 0.7  +
     euro5    * 0.6
    ) as weight
from (
select
tq.bills_money_bag_id,
tq.euro500,
tq.euro200,
tq.euro100,
tq.euro50 ,
tq.euro20 ,
tq.euro10 ,
tq.euro5
  FROM tronc_queteur as tq
  WHERE tq.ul_id              = :ul_id
  AND   tq.bills_money_bag_id = :bills_money_bag_id
  AND   tq.deleted            = 0
UNION
select
nd.bills_money_bag_id,
nd.euro500,
nd.euro200,
nd.euro100,
nd.euro50 ,
nd.euro20 ,
nd.euro10 ,
nd.euro5
  FROM named_donation as nd
  WHERE nd.ul_id              = :ul_id2
  AND   nd.bills_money_bag_id = :bills_money_bag_id2
  AND   nd.deleted            = 0
) as union_select
";

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters,function($row) {
      return new BillsMoneyBagSummaryEntity($row, $this->logger);
    });
  }
}
