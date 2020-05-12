<?php
namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\MailingInfoEntity;
use RedCrossQuest\Entity\MailingSummaryEntity;

class MailingDBService extends DBService
{
  /**
   * Get stats about unsent, sent and sent with error emails per sectors.
   *
   * @param int $ulId the Id of the Unite Local
   * @return MailingSummaryEntity[][] list of summaries
   * @throws PDOException if the query fails to execute on the server
   */
  public function getMailingSummary(int $ulId):array
  {
    $parameters = ["ul_id" => $ulId];

    $summary = [];

//-- email non envoyé par secteurs
    $sql = "
select secteur, count(1) as count
from queteur q
where q.ul_id = :ul_id
AND q.anonymization_token is null
AND q.mailing_preference = 1
and q.id not IN ( 
  select queteur_id 
  from queteur_mailing_status 
  where year=year(now()) 
)
AND q.id IN (
  select queteur_id
  from tronc_queteur
  where year(comptage) = year(now())
  and deleted = 0
)
group by secteur 
order by secteur";


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);


    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new MailingSummaryEntity($row, $this->logger);
    }

    $summary["UNSENT_EMAIL"] = $results;


// email successfully sent
    $sql = "
select  secteur, count(1) as count
from queteur q
where q.ul_id = :ul_id
AND q.anonymization_token is null
AND q.mailing_preference = 1
AND q.id IN ( 
  select queteur_id 
  from queteur_mailing_status 
  where year=year(now()) 
  and status_code = '202')
  AND q.id IN (
  select queteur_id
  from tronc_queteur
  where year(comptage) = year(now())
  and deleted = 0
)
group by secteur
order by secteur";


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);


    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new MailingSummaryEntity($row, $this->logger);
    }

    $summary["EMAIL_SUCCESS"] = $results;


//email sent with error
    $sql = "
select secteur, count(1) as count
from queteur q
where q.ul_id = :ul_id
AND q.anonymization_token is null
AND q.mailing_preference = 1
and q.id IN ( 
  select queteur_id 
  from queteur_mailing_status 
  where year=year(now()) 
  and status_code != '202')
  AND q.id IN (
  select queteur_id
  from tronc_queteur
  where year(comptage) = year(now())
  and deleted = 0
)
group by secteur
order by secteur";


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);


    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new MailingSummaryEntity($row, $this->logger);
    }

    $summary["EMAIL_ERROR"] = $results;


    return $summary;
  }


  /**
   * Get emailing information (names email etc...)
   *
   * @param int $ulId the Id of the Unite Local
   * @param int $pagingSize the number of email sent at once
   * @return MailingInfoEntity[] list of summaries
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if some parsing error occurs
   */
  public function getMailingInfo(int $ulId, int $pagingSize):array
  {
    $parameters = ["ul_id" => $ulId];

    $sql = "
select id, first_name, last_name, email, secteur, man, spotfire_access_token
from queteur q
where q.ul_id = :ul_id
AND   q.anonymization_token is null
AND   q.mailing_preference = 1
-- if in mailing status, it means it has already been sent (in error or success)
and q.id not IN 
( 
  select queteur_id 
  from   queteur_mailing_status 
  where  year=year(now())
)
-- queteur has participated this year
AND q.id IN 
(
  select queteur_id
  from   tronc_queteur
  where  year(comptage) = year(now())
  and    deleted = 0
)
-- benevole first, if there is an error, it's less a problem
order by q.secteur, q.id
LIMIT 0, $pagingSize";


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);


    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new MailingInfoEntity($row, $this->logger);
    }

    return $results;
  }

  /**
   * Insert one queteur_mailing_status

   * @param int    $queteur_id    Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param string $status_code   Status code of the send email
   * @throws PDOException if the query fails to execute on the server
   */
  public function insertQueteurMailingStatus(int $queteur_id, string $status_code):void
  {

    $queryData = [
      'queteur_id'=> $queteur_id,
      'status_code'=> $status_code
    ];

      $sql = "
INSERT INTO `queteur_mailing_status`
(
  `queteur_id`,
  `year`,
  `status_code`,
  `email_send_date`
)
VALUES
(
:queteur_id,
YEAR(NOW()),
:status_code,
NOW()
);
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($queryData);
    $stmt->closeCursor();
  }


  /**
   * Update queteur with the spotfire access token
   * Should only be called if there's no existing spotfire token
   *
   * @param string $spotfireAccessToken the spotfire access token
   * @param int    $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int    $queteurId     Id of the queteur
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateQueteurWithSpotfireAccessToken(string $spotfireAccessToken, int $queteurId, int $ulId):void
  {
    $queryData = [
      'id'                   => $queteurId,
      'ul_id'                => $ulId     ,
      'spotfire_access_token'=> $spotfireAccessToken
    ];

    $sql = "
UPDATE `queteur`
SET   `spotfire_access_token`     = :spotfire_access_token                      
WHERE `id`    = :id
AND   `ul_id` = :ul_id  
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute($queryData);

    $stmt->closeCursor();
  }


  /**
   * Update queteur_mailing_status->spotfire_opened to 1, to mark the fact that the spotfire graph has been opened.
   *
   * @param string $guid the GUID of the spotfire_access_token from queteur table
   * @throws PDOException if the query fails to execute on the server
   */
  public function confirmRead(string $guid):void
  {
    $this->logger->info("Updating spotfire_open to 1 for queteur", ["guid"=>$guid]);

    $queryData = [
      'guid'                   => $guid
    ];

    $sql = "
UPDATE `queteur_mailing_status` qms
INNER JOIN  queteur            q
ON          qms.`queteur_id` = q.`id`
SET   qms.`spotfire_opened`       = 1,
      qms.`spotfire_open_date`    = NOW()                      
WHERE   q.`spotfire_access_token` = :guid
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute($queryData);

    $stmt->closeCursor();
  }
}
