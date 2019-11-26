<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\routes\routesActions\Action;


abstract class AuthenticateAbstractAction extends Action
{
  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  //generate a JWT for a user
  protected function getToken(QueteurEntity $queteur, UniteLocaleEntity $ul, UserEntity $user) : Token
  {
    $jwtSecret      = $this->settings['jwt'        ]['secret'        ];
    $issuer         = $this->settings['jwt'        ]['issuer'        ];
    $audience       = $this->settings['jwt'        ]['audience'      ];
    $deploymentType = $this->settings['appSettings']['deploymentType'];
    $sessionLength  = $this->settings['appSettings']['sessionLength' ];

    $signer   = new Sha256();
    $issuedAt =   time() ;

    $jwtToken = (new Builder())
      ->issuedBy          ($issuer  ) // Configures the issuer (iss claim)
      ->permittedFor      ($audience)
      ->issuedAt          ($issuedAt)
      ->canOnlyBeUsedAfter($issuedAt)
      ->expiresAt         (time() + $sessionLength * 3600)
      ->withClaim         ('username' , $queteur->nivol)
      ->withClaim         ('id'       , $user->id      )
      ->withClaim         ('ulId'     , $queteur->ul_id)
      ->withClaim         ('ulName'   , $ul->name      )
      ->withClaim         ('ulMode'   , $ul->mode      )
      ->withClaim         ('queteurId', $queteur->id   )
      ->withClaim         ('roleId'   , $user->role    )
      ->withClaim         ('d'        , $deploymentType)
      ->getToken          ($signer          , new Key($jwtSecret));
    return $jwtToken;
  }
}
