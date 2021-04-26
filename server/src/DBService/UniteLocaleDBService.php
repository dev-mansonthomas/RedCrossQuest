<?php /** @noinspection SpellCheckingInspection */

namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\Entity\PageableResponseEntity;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;

class UniteLocaleDBService extends DBService
{

  /**
   * Get one UniteLocale by its ID
   *
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return UniteLocaleEntity  The Unite Locale
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getUniteLocaleById(int $ulId):UniteLocaleEntity
  {
    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`,
        `ul`.`president_man`,
        `ul`.`president_nivol`,
        `ul`.`president_first_name`,
        `ul`.`president_last_name`,
        `ul`.`president_email`,
        `ul`.`president_mobile`,
        `ul`.`tresorier_man`,
        `ul`.`tresorier_nivol`,
        `ul`.`tresorier_first_name`,
        `ul`.`tresorier_last_name`,
        `ul`.`tresorier_email`,
        `ul`.`tresorier_mobile`,
        `ul`.`admin_man`,
        `ul`.`admin_nivol`,
        `ul`.`admin_first_name`,
        `ul`.`admin_last_name`,
        `ul`.`admin_email`,
        `ul`.`admin_mobile`
FROM    `ul`
WHERE   `ul`.id    = :ul_id
";

    $parameters = ["ul_id" => $ulId];
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new UniteLocaleEntity($row, $this->logger);
    }, true);
  }

  public static $updateUniteLocaleSQL = "
UPDATE `ul`
SET
  `email`                = :email,
  `name`                 = :name,
  `phone`                = :phone,
  `address`              = :address,
  `postal_code`          = :postal_code ,
  `city`                 = :city,
  `longitude`            = :longitude,
  `latitude`             = :latitude,
  `publicDashboard`      = :publicDashboard,
  `president_man`        = :president_man,       
  `president_nivol`      = :president_nivol,     
  `president_first_name` = :president_first_name,
  `president_last_name`  = :president_last_name, 
  `president_email`      = :president_email,     
  `president_mobile`     = :president_mobile,    
  `tresorier_man`        = :tresorier_man,       
  `tresorier_nivol`      = :tresorier_nivol,     
  `tresorier_first_name` = :tresorier_first_name,
  `tresorier_last_name`  = :tresorier_last_name, 
  `tresorier_email`      = :tresorier_email,     
  `tresorier_mobile`     = :tresorier_mobile,    
  `admin_man`            = :admin_man,           
  `admin_nivol`          = :admin_nivol,         
  `admin_first_name`     = :admin_first_name,    
  `admin_last_name`      = :admin_last_name,     
  `admin_email`          = :admin_email,         
  `admin_mobile`         = :admin_mobile       
WHERE `id`               = :id
";

  /**
   * Update UL name and address
   *
   * @param UniteLocaleEntity $ul The UL info
   * @param int $ulId The id of the UL to be updated
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function updateUL(UniteLocaleEntity $ul, int $ulId):void
  {
    $parameters = [
      "email"                => $ul->email,
      "name"                 => $ul->name,
      "phone"                => $ul->phone,
      "address"              => $ul->address,
      "postal_code"          => $ul->postal_code,
      "city"                 => $ul->city,
      "longitude"            => $ul->longitude,
      "latitude"             => $ul->latitude,
      "publicDashboard"      => $ul->publicDashboard,
      "president_man"        => $ul->president_man == 1 ?  1 : 0,
      "president_nivol"      => $ul->president_nivol,
      "president_first_name" => $ul->president_first_name,
      "president_last_name"  => $ul->president_last_name,
      "president_email"      => $ul->president_email,
      "president_mobile"     => $ul->president_mobile,
      "tresorier_man"        => $ul->tresorier_man == 1 ?  1 : 0,
      "tresorier_nivol"      => $ul->tresorier_nivol,
      "tresorier_first_name" => $ul->tresorier_first_name,
      "tresorier_last_name"  => $ul->tresorier_last_name,
      "tresorier_email"      => $ul->tresorier_email,
      "tresorier_mobile"     => $ul->tresorier_mobile,
      "admin_man"            => $ul->admin_man == 1 ?  1 : 0,
      "admin_nivol"          => $ul->admin_nivol,
      "admin_first_name"     => $ul->admin_first_name,
      "admin_last_name"      => $ul->admin_last_name,
      "admin_email"          => $ul->admin_email,
      "admin_mobile"         => $ul->admin_mobile,
      "id"                   => $ulId
    ];

    $this->executeQueryForUpdate(self::$updateUniteLocaleSQL, $parameters);
  }

  /**
   * Update the UL date of first use of RCQ
   *
   * @param int $ulId The id of the UL to be updated
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function updateULDateDemarrageRCQ(int $ulId):void
  {
    $sql = "UPDATE `ul`
SET
  `date_demarrage_rcq`   = NOW()  
WHERE `id`               = :id";
    $parameters = [
      "id"                   => $ulId
    ];

    $this->executeQueryForUpdate($sql, $parameters);
  }




  /**
   * Update UL registration approval info
   *
   * @param UniteLocaleEntity $ul The UL info
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function updateULRegistration(UniteLocaleEntity $ul)
  {
    $sql = "
UPDATE `ul_registration`
SET
  `registration_approved` = :registration_approved,
  `reject_reason`         = :reject_reason,
  `approval_date`         = NOW()       
WHERE `id`                = :id
";

    $parameters = [
      "registration_approved" => $ul->registration_approved == 1 ?  1 : 0,
      "reject_reason"         => $ul->reject_reason,
      "id"                    => $ul->registration_id
    ];

    $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Register a new UL
   *
   * @param UniteLocaleEntity $ul The UL info
   * @return  int the ID of the registration
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function registerNewUL(UniteLocaleEntity $ul):int
  {

    $sql = "
INSERT INTO  `ul_registration`
(
  `ul_id`               ,
  `president_man`       ,
  `president_nivol`     ,
  `president_first_name`,
  `president_last_name` ,
  `president_email`     ,
  `president_mobile`    ,
  `tresorier_man`       ,
  `tresorier_nivol`     ,
  `tresorier_first_name`,
  `tresorier_last_name` ,
  `tresorier_email`     ,
  `tresorier_mobile`    ,
  `admin_man`           ,
  `admin_nivol`         ,
  `admin_first_name`    ,
  `admin_last_name`     ,
  `admin_email`         ,
  `admin_mobile`        ,
  `registration_token`
)
VALUES
(
  :ul_id,
  :president_man,       
  :president_nivol,      
  :president_first_name, 
  :president_last_name,  
  :president_email,      
  :president_mobile,     
  :tresorier_man,        
  :tresorier_nivol,      
  :tresorier_first_name, 
  :tresorier_last_name,  
  :tresorier_email,      
  :tresorier_mobile,     
  :admin_man,            
  :admin_nivol,          
  :admin_first_name,     
  :admin_last_name,      
  :admin_email,          
  :admin_mobile,
  :registration_token
)
";
    $parameters = [
      "ul_id"                => $ul->id,
      "president_man"        => $ul->president_man == 1 ?  1 : 0,
      "president_nivol"      => $ul->president_nivol,
      "president_first_name" => $ul->president_first_name,
      "president_last_name"  => $ul->president_last_name,
      "president_email"      => $ul->president_email,
      "president_mobile"     => $ul->president_mobile,
      "tresorier_man"        => $ul->tresorier_man == 1 ?  1 : 0,
      "tresorier_nivol"      => $ul->tresorier_nivol,
      "tresorier_first_name" => $ul->tresorier_first_name,
      "tresorier_last_name"  => $ul->tresorier_last_name,
      "tresorier_email"      => $ul->tresorier_email,
      "tresorier_mobile"     => $ul->tresorier_mobile,
      "admin_man"            => $ul->admin_man == 1 ?  1 : 0,
      "admin_nivol"          => $ul->admin_nivol,
      "admin_first_name"     => $ul->admin_first_name,
      "admin_last_name"      => $ul->admin_last_name,
      "admin_email"          => $ul->admin_email,
      "admin_mobile"         => $ul->admin_mobile,
      "registration_token"   => $ul->registration_token
    ];

    return $this->executeQueryForInsert($sql, $parameters, true);
  }


  /**
   * Search unite locale by name, postal code, city
   *
   * @param string $query : the criteria to search UL on name postal code city and president, tresorier, admin first & last name
   * @return UniteLocaleEntity[]  the list of UniteLocale
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function searchUniteLocale(string $query):array
  {
    if ($query === null)
      return [];

    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`
FROM    `ul`
WHERE   UPPER(ul.`name`                ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`postal_code`         ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`city`                ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`president_first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`president_last_name` ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`admin_first_name`    ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`admin_last_name`     ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`tresorier_first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`tresorier_last_name` ) like concat('%', UPPER(:query), '%')
";
    $parameters = ["query" => $query];

    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new UniteLocaleEntity($row, $this->logger);
    });
  }

  /**
   * Search unite locale by name, postal code, city
   *
   * @param string $query : the criteria to search UL on name postal code city and president, tresorier, admin first & last name
   * @return UniteLocaleEntity[]  the list of UniteLocale
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function validateULRegistration(string $registration_token, int $ul_id, int $registration_id):int
  {
    $sql = "
SELECT count(1) as rowcount
FROM   ul_registration `ulr`
WHERE  `ulr`.id                    = :registration_id
AND    `ulr`.ul_id                 = :ul_id
AND    `ulr`.registration_token    = :registration_token
AND    `ulr`.registration_approved is null
AND    `ulr`.approval_date         is null
";
    $parameters = [
      "registration_id"   => $registration_id,
      "ul_id"             => $ul_id,
      "registration_token"=> $registration_token
    ];

    return $this->getCountForSQLQuery($sql, $parameters);
  }

  /**
   * Search unite locale by name, postal code, city that are not registered
   *
   * @param string $query : the criteria to search UL on name postal code city and president, tresorier, admin first & last name
   * @return UniteLocaleEntity[]  the list of UniteLocale
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function searchUnRegisteredUniteLocale(string $query):array
  {
    if ($query === null)
      return [];

    /**
     * Select all UL that are not yet registred or the registration is incomplete
     *
     * It select all UL that
     * * have a date_demarrage_rcq NULL (not currently using)
     * * and
     *      there's no registration (registration_token is null)
     *      or
     *      there's a registration, but not yet completed :  approval_date is null but there's a registration token
     *
     */
    $sql = "
SELECT  `ul` .`id`,
        `ul` .`name`,
        `ul` .`postal_code`,
        `ul` .`city`,
        LENGTH(`ulr`.registration_token) as registration_in_progress,
        `ulr`.id                         as registration_id
FROM    `ul`
LEFT OUTER JOIN  `ul_registration` ulr on `ul`.`id` = ulr.ul_id
WHERE   `ul` .`date_demarrage_rcq` is NULL
and     `ulr`.approval_date        is NULL
AND (
        UPPER(ul.`name`       ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`postal_code`) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`city`       ) like concat('%', UPPER(:query), '%')
)
AND  `ul`.id > 1
ORDER BY `ul`.`name`
LIMIT 0,20
"; //ul with id 1 is a fake UL for data integrity
    $parameters = ["query" => $query];

    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new UniteLocaleEntity($row, $this->logger, true);
    });
  }


  /**
   * List UL registration by status (new registration, validated, rejected)
   *
   * @param PageableRequestEntity $pageableRequest
   * @return PageableResponseEntity the list of UniteLocale
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function listULRegistrations(PageableRequestEntity $pageableRequest):PageableResponseEntity
  {
    /** @var int $registrationStatus */
    $registrationStatus = $pageableRequest->filterMap['registration_status'];

    $registrationStatusSQL = null;

    if ($registrationStatus == 0 || $registrationStatus == null)
    {
      $registrationStatusSQL = "AND registration_approved is null";
    }
    else if ($registrationStatus == 1)
    {
      $registrationStatusSQL = "AND registration_approved = 1";
    }
    else if ($registrationStatus == 2)
    {
      $registrationStatusSQL = "AND registration_approved = 0";
    }


    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`,
        ulr.`id` as registration_id,
        ulr.`president_man`,
        ulr.`president_nivol`,
        ulr.`president_first_name`,
        ulr.`president_last_name`,
        ulr.`president_email`,
        ulr.`president_mobile`,
        ulr.`tresorier_man`,
        ulr.`tresorier_nivol`,
        ulr.`tresorier_first_name`,
        ulr.`tresorier_last_name`,
        ulr.`tresorier_email`,
        ulr.`tresorier_mobile`,
        ulr.`admin_man`,
        ulr.`admin_nivol`,
        ulr.`admin_first_name`,
        ulr.`admin_last_name`,
        ulr.`admin_email`,
        ulr.`admin_mobile`,
        ulr.`created`
FROM    `ul`, `ul_registration` ulr
WHERE   ulr.ul_id = `ul`.id
$registrationStatusSQL
";
    $parameters = [];

    $count   = $this->getCountForSQLQuery ($sql, $parameters);
    $results = $this->executeQueryForArray($sql, $parameters, function($row) {
      return new UniteLocaleEntity($row, $this->logger);
    }, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);

    return new PageableResponseEntity($count, $results, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);
  }


  /**
   * get an UL registration
   *
   * @param int $ulRegistrationId
   * @return UniteLocaleEntity the registration details
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getULRegistration(int $ulRegistrationId):UniteLocaleEntity
  {

    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`,
        ulr.`id` as registration_id,
        ulr.`president_man`,
        ulr.`president_nivol`,
        ulr.`president_first_name`,
        ulr.`president_last_name`,
        ulr.`president_email`,
        ulr.`president_mobile`,
        ulr.`tresorier_man`,
        ulr.`tresorier_nivol`,
        ulr.`tresorier_first_name`,
        ulr.`tresorier_last_name`,
        ulr.`tresorier_email`,
        ulr.`tresorier_mobile`,
        ulr.`admin_man`,
        ulr.`admin_nivol`,
        ulr.`admin_first_name`,
        ulr.`admin_last_name`,
        ulr.`admin_email`,
        ulr.`admin_mobile`,
        ulr.`created`,
        ulr.`registration_approved`,
        ulr.`reject_reason`,
        ulr.`approval_date`
FROM    `ul`, `ul_registration` ulr
WHERE   ulr.ul_id = `ul`.id
AND     ulr.id = :registration_id
";
    $parameters = ["registration_id"=> $ulRegistrationId];

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new UniteLocaleEntity($row, $this->logger);
    });
  }
}
