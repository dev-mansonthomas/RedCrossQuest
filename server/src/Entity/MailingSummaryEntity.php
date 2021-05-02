<?php
namespace RedCrossQuest\Entity;


use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="MailingSummaryEntity", required={"secteur", "count"})
 */
class MailingSummaryEntity extends Entity
{
  /**
   * @OA\Property()
   * @var int $secteur Id of the Secteur {id:1,label:'Action Sociale'},{id:2,label:'Secours'},{id:3,label:'Bénévole d\'un Jour'},{id:4,label:'Ancien Bénévole, Inactif ou Adhérent'},{id:5,label:'Commerçant'},{id:6,label:'Spécial'}
   */
  public $secteur          ;
  /**
   * @OA\Property()
   * @var int $count Number of
   */
  public $count            ;

  protected array $_fieldList = ['secteur', 'count'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('secteur'         , $data);
    $this->getInteger('count'           , $data);
  }
}
