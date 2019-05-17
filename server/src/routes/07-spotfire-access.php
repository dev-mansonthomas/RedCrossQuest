<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';
use \RedCrossQuest\Service\Logger;
use \RedCrossQuest\Entity\LoggingEntity;

/**
 * fetch an existing token for the user
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/graph', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  //$this->logger->debug("generating spotfire access for ");
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $userId = $decodedToken->getUid   ();

    $validToken = $this->spotfireAccessDBService->getValidToken($userId, $ulId);

    return $response->getBody()->write(json_encode($validToken));
  }
  catch(\Exception $e)
  {
      $this->logger->error("Error while getting current Token for user ($userId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
      throw $e;
  }
});




/*
 Documentation :



Au click sur un graph Spotfire, l’application insert une ligne dans la table spotfire_access.
Avec un token, une date d’expiration, l’UL_ID répliqué eviter des jointures inutiles, et le user_id (jointure vers table user pour checker s’il est actif et récupérer son role)

$spotfire_access_table = $this->table('spotfire_access');
$spotfire_access_table
  ->addColumn('token'           , 'string', array('limit' => 36))
  ->addColumn('token_expiration', 'datetime')
  ->addColumn('ul_id'           , 'integer')
  ->addColumn('user_id'         , 'integer')

  ->addForeignKey('ul_id'  , 'ul'   , 'id')
  ->addForeignKey('user_id', 'users', 'id')
  ->create();



Spotfire est mise à jour toutes les 2 minutes, minutes paires.
L’application RCQ attends que la prochaine minutes paires soit passée, puis ouvre le graph spotfire avec le token en parametre.
Le calcul de la colonne show=true se fait avec les critères suivant:
* Token trouvé dans la table spotfire_access
* La date courante est avant la date d’expiration
* l’utilisateur est enabled
* le role_id est supérieur parametre du dashboard.


 * */
