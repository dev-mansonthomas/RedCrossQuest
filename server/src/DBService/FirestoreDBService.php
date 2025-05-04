<?php
namespace RedCrossQuest\DBService;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use RedCrossQuest\Service\Logger;

abstract class FirestoreDBService
{
  /** @var FirestoreClient $firstoreClient*/
  protected FirestoreClient $firestoreClient;
  /** @var Logger $logger*/
  protected Logger $logger;

  /** @var string $FIRESTORE_COLLECTION*/
  protected string $FIRESTORE_COLLECTION;

  /** @var CollectionReference $firestoreCollection*/
  protected CollectionReference $firestoreCollection;

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
