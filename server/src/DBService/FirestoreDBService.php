<?php
namespace RedCrossQuest\DBService;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use RedCrossQuest\Service\Logger;

require '../../vendor/autoload.php';

abstract class FirestoreDBService
{
  /** @var FirestoreClient $firstoreClient*/
  protected $firestoreClient;
  /** @var Logger $logger*/
  protected $logger;

  /** @var string $FIRESTORE_COLLECTION*/
  protected $FIRESTORE_COLLECTION;

  /** @var CollectionReference $firestoreCollection*/
  protected $firestoreCollection;

  public function __construct(FirestoreClient $firestoreClient, Logger $logger)
  {
    $this->firestoreClient = $firestoreClient;
    $this->logger         = $logger;
  }

  public function initCollection():void
  {
    $this->firestoreCollection = $this->firestoreClient->collection($this->FIRESTORE_COLLECTION);
  }
}
