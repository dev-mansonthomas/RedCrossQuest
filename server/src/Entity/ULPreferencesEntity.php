<?php
namespace RedCrossQuest\Entity;

use Exception;
use Google\Cloud\Firestore\DocumentSnapshot;
use Psr\Log\LoggerInterface;

/**
 * Data is stored in Firestore, not MySQL
 */
class ULPreferencesEntity extends Entity
{
  /** @var string $FIRESTORE_DOC_ID firestore internal ID*/
  public $FIRESTORE_DOC_ID          ;
  /** @var int $ul_id UL ID*/
  public $ul_id                     ;
  /** @var bool $rq_display_daily_stats Display in RedQuest the dailystats or not*/
  public $rq_display_daily_stats    ;
  /** @var string $rq_display_queteur_ranking Display the ranking of queteur : no, 1st page, all pages (see statics var)*/
  public $rq_display_queteur_ranking    ;
  /** @var bool $use_bank_bag use bank moneybag or not (mandatory field in tronc_queteur comptage)*/
  public $use_bank_bag              ;
  /** @var bool $rq_autonomous_depart_and_return Can volunteers set the depart & return date themselves with RedQuest*/
  public $rq_autonomous_depart_and_return;

  public static $RQ_DISPLAY_QUETE_STATS_NONE      = "NONE"    ;
  public static $RQ_DISPLAY_QUETE_STATS_1ST_PAGE  = "1ST_PAGE";
  public static $RQ_DISPLAY_QUETE_STATS_ALL       = "ALL"     ;
  
  protected $_fieldList = ['ul_id', 'rq_display_daily_stats', 'rq_display_queteur_ranking', 'use_bank_bag', 'rq_autonomous_depart_and_return'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data
   * @param LoggerInterface $logger
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('ul_id'                          , $data);
    $this->getBoolean('rq_display_daily_stats'         , $data);
    $this->getBoolean('use_bank_bag'                   , $data);
    $this->getBoolean('rq_autonomous_depart_and_return', $data);
    $this->getString ('rq_display_queteur_ranking'     , $data, 8);
  }

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param DocumentSnapshot $documentSnapshot The data to use to create
   * @param LoggerInterface $logger
   * @return ULPreferencesEntity
   * @throws Exception if a parse Date or JSON fails
   */
  public static function withFirestoreDocument(DocumentSnapshot $documentSnapshot, LoggerInterface $logger)
  {
    $instance = new ULPreferencesEntity($documentSnapshot->data(), $logger);
    $instance->FIRESTORE_DOC_ID = $documentSnapshot->reference()->id();
    return $instance;
  }

  /**
   *
   * Init a class from an array (creating the firestore document)
   *
   * @param array $data associative array matching this object property name
   * @param LoggerInterface $logger
   * @return ULPreferencesEntity
   */
  public static function withArray(array $data, LoggerInterface $logger)
  {
    $instance = new ULPreferencesEntity($data, $logger);
    $instance->FIRESTORE_DOC_ID = null;
    return $instance;
  }
}
