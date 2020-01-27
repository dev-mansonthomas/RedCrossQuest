<?php




namespace RedCrossQuest\routes\routesActions\namedDonations;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class ListNamedDonations extends Action
{
  /**
   * @var NamedDonationDBService        $namedDonationDBService
   */
  private $namedDonationDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param NamedDonationDBService        $namedDonationDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              NamedDonationDBService        $namedDonationDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->namedDonationDBService = $namedDonationDBService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId   = $this->decodedToken->getUlId();
    $roleId = $this->decodedToken->getRoleId();


    $validations = [
      ClientInputValidatorSpecs::withString ("q"       ,  $this->getParam('q'               ), 100 , false    ),
      ClientInputValidatorSpecs::withBoolean("deleted" ,  $this->getParam('deleted'         ), false  , false),
      ClientInputValidatorSpecs::withInteger("year"    ,  $this->getParam('year'            ), 2050 , false    )
    ];

    if(array_key_exists('admin_ul_id',$this->queryParams) && $roleId == 9)
    {
      $validations [] = ClientInputValidatorSpecs::withInteger('admin_ul_id',$this->getParam('admin_ul_id'), 1000, true);
    }

    $this->validateSentData($validations);

    $query     = $this->validatedData["q"];
    $deleted   = $this->validatedData["deleted"];
    $year      = $this->validatedData["year"];
    $adminUlId = null;
    if(array_key_exists('admin_ul_id',$this->queryParams) && $roleId == 9)
    {
      $adminUlId = $this->validatedData["admin_ul_id"];
    }

    $this->logger->info("searching named donation", array('q'=>$query, 'deleted'=>$deleted, 'year'=>$year, 'admin_ul_id'=>$adminUlId));
    $namedDonations = $this->namedDonationDBService->getNamedDonations($query, $deleted, $year, $adminUlId!= null && $roleId == 9? $adminUlId : $ulId);

    $this->response->getBody()->write(json_encode($namedDonations));

    return $this->response;
  }
}
