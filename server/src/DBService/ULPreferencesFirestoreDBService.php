<?php
namespace RedCrossQuest\DBService;

use Exception;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use RedCrossQuest\Entity\ULPreferencesEntity;
use RedCrossQuest\Service\Logger;
use UnexpectedValueException;


class ULPreferencesFirestoreDBService extends FirestoreDBService
{

  public function __construct(FirestoreClient $firestoreClient, Logger $logger)
  {
    parent::__construct($firestoreClient, $logger);
    $this->FIRESTORE_COLLECTION = "ul_prefs";
    $this->initCollection();
  }

  /**
   * Get the preference of the UL from Firestore
   * @param int $ul_id
   * @return ULPreferencesEntity|null
   * @throws Exception
   */
  public function getULPrefs(int $ul_id):?ULPreferencesEntity
  {
    $query     = $this->firestoreCollection->where('ul_id', '=', $ul_id);
    $documents = $query->documents();

    /** @var DocumentSnapshot $document*/
    foreach ($documents as $document)
    {
      if ($document->exists())
      {
        return ULPreferencesEntity::withFirestoreDocument($document, $this->logger);
      }
    }
    return null;
  }

  /**
   * Update the preference of the UL to Firestore
   * @param int $ul_id
   * @param ULPreferencesEntity $ulPrefs
   */
  public function updateUlPrefs(int $ul_id, ULPreferencesEntity $ulPrefs):void
  {
    $dataForFirestore = $ulPrefs->prepareDataForFirestoreUpdate();

    //remove non RQ properties
    unset($dataForFirestore['token_benevole']);
    unset($dataForFirestore['token_benevole_1j']);
    //unset($dataForFirestore['use_bank_bag']);


    if($ulPrefs->ul_id != $ul_id)
    {
      throw new UnexpectedValueException("Attempt to update prefs from another UL. user from ul : $ul_id attempt to update UL ID : $ulPrefs->ul_id, with data ".json_encode($dataForFirestore));
    }
    if($ulPrefs->FIRESTORE_DOC_ID)
    {//update
      $this->firestoreCollection->document($ulPrefs->FIRESTORE_DOC_ID)->set($dataForFirestore);
    }
    else
    {//insert
      $this->firestoreCollection->newDocument()->set($dataForFirestore);
    }
  }
}
