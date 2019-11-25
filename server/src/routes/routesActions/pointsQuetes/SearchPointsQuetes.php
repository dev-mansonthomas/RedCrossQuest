<?php




namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class SearchPointsQuetes extends Action
{
  /**
   * @var PointQueteDBService     $pointQueteDBService
   */
  private $pointQueteDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param PointQueteDBService     $pointQueteDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              PointQueteDBService     $pointQueteDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->pointQueteDBService = $pointQueteDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString ("q"               , $this->getParam('q'               ), 40  , false    ),
        ClientInputValidatorSpecs::withInteger("point_quete_type", $this->getParam('point_quete_type'), 10   , false    ),
        ClientInputValidatorSpecs::withBoolean("active"          , $this->getParam('active'          ), false  , true ),
        ClientInputValidatorSpecs::withInteger('admin_ul_id'     , $this->getParam('admin_ul_id'     ), 1000 , false    )
      ]);

    $query            = $this->validatedData["q"];
    $point_quete_type = $this->validatedData["point_quete_type"];
    $active           = $this->validatedData["active"];
    $admin_ul_id      = $this->validatedData["admin_ul_id"];
    $ulId             = $this->decodedToken->getUlId   ();
    $roleId           = $this->decodedToken->getRoleId ();

    if($admin_ul_id != null && $roleId == 9)
    {
      $ulId = $admin_ul_id;
    }

    $pointQuetes = $this->pointQueteDBService->searchPointQuetes($query, $point_quete_type, $active, $ulId);

    $this->response->getBody()->write(json_encode($pointQuetes));

    return $this->response;
  }
}