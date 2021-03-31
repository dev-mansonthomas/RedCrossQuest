<?php
namespace RedCrossQuest\routes\routesActions\authentication;

use DateTimeImmutable;
use DI\Annotation\Inject;
use Google\ApiCore\ApiException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


abstract class AuthenticateAbstractAction extends Action
{
  /**
   * @Inject("settings")
   * @var array settings
   */
  protected array $settings;
  /** @var Configuration           $JWTConfiguration*/
  private Configuration           $JWTConfiguration;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param Configuration           $JWTConfiguration
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              Configuration           $JWTConfiguration)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->JWTConfiguration = $JWTConfiguration;
  }


  //generate a JWT for a user

  /**
   * @param QueteurEntity $queteur
   * @param UniteLocaleEntity $ul
   * @param UserEntity $user
   * @return Token
   * @throws ApiException
   */
  protected function getToken(QueteurEntity $queteur, UniteLocaleEntity $ul, UserEntity $user) : Token
  {

    $issuer         = $this->settings['jwt'        ]['issuer'        ];
    $audience       = $this->settings['jwt'        ]['audience'      ];
    $deploymentType = $this->settings['appSettings']['deploymentType'];
    $sessionLength  = $this->settings['appSettings']['sessionLength' ];

    $now    = new DateTimeImmutable();

    return $this->JWTConfiguration->builder()
      ->issuedBy          ($issuer  ) // Configures the issuer (iss claim)
      ->permittedFor      ($audience)
      ->issuedAt          ($now)
      ->canOnlyBeUsedAfter($now->modify('+1 minute'))
      ->expiresAt         ($now->modify("+$sessionLength hour"))
      ->withClaim         ('username' , $queteur->nivol)
      ->withClaim         ('id'       , $user->id      )
      ->withClaim         ('ulId'     , $queteur->ul_id)
      ->withClaim         ('ulName'   , $ul->name      )
      ->withClaim         ('ulMode'   , $ul->mode      )
      ->withClaim         ('queteurId', $queteur->id   )
      ->withClaim         ('roleId'   , $user->role    )
      ->withClaim         ('d'        , $deploymentType)
      ->getToken          ($this->JWTConfiguration->signer() , $this->JWTConfiguration->signingKey());
  }
}
