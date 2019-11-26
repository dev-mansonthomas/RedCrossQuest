<?php
declare(strict_types=1);
/*
 * MAKE CHANGES IN /Users/tom/.cred/rcq-fr-local-settings.php
 * THIS FILE WILL BE OVERWRITTEN AFTER EACH DEPLOY
 *
 *
 */
putenv("GOOGLE_APPLICATION_CREDENTIALS=/Users/thomasmanson/.cred/rcq-fr-dev-61e86fa56dc1.json");
putenv('MYSQL_DSN=mysql:host=127.0.0.1;port=3306;dbname=rcq;charset=utf8;');
putenv('MYSQL_USER=rcq'    );
putenv('MYSQL_PASSWORD=rcq');
putenv('JWT_SECRET=secret-rcq'     );
putenv('JWT_ISSUER=https://rcq.paquerette.com'     );
putenv('JWT_ISSUER=https://rcq.paquerette.com'     );
putenv('APP_URL=http://localhost:3000/') ;
putenv('REDQUEST_DOMAIN=redque.st') ;
putenv('APP_ENV=D') ;
putenv('GOOGLE_MAPS_API=AIzaSyDRSCQ3y4NGXfQm_gD_uP_IyYXc2sZWeJY');
putenv('SENDGRID_API_KEY=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');
putenv('SENDGRID_SENDER=support@redcrossquest.com');
putenv('RECAPTCHA_SECRET=6LfotpwUAAAAAJpKkGZEbU7pDVdC_D5_ihqa5yJ8');

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  // Global Settings Object
  $containerBuilder->addDefinitions([
    'settings' => [
      'displayErrorDetails' => true, // set to false in production
      // logging settings
      'logger'              => [
        'name'      => 'RCQ' ,
        'level'     => 'info'
      ],
      'db'          => [
        'dsn'  => getenv('MYSQL_DSN'     ),
        'user' => getenv('MYSQL_USER'    ),
        'pwd'  => getenv('MYSQL_PASSWORD')
      ],
      'PubSub'      => [
        'tronc_queteur_update_topic' => 'tronc_queteur_update',
        'queteur_approval_topic'     => 'queteur_approval_topic',
        'ul_update_topic'            => 'ul_update'
      ],
      'jwt'         => [
        'secret'   => getenv('JWT_SECRET'     ),
        'issuer'   => getenv('JWT_ISSUER'     ),
        'audience' => getenv('JWT_ISSUER'     )
      ],
      'ReCaptcha'   => [
        'secret'                => getenv('RECAPTCHA_SECRET'),
        'lowestAcceptableScore' => 0.7
      ],
      'appSettings' => [
        'sessionLength'    => 6                                         , //hours
        'appUrl'           => getenv('APP_URL')                         ,
        'RedQuestDomain'   => getenv('REDQUEST_DOMAIN')                 ,
        'resetPwdPath'     => '#!/resetPassword?key='                   ,
        'deploymentType'   => getenv('APP_ENV')                         ,   //D:Dev, T:Testing, P:Production,
        'gmapAPIKey'       => getenv('GOOGLE_MAPS_API')                 ,
        'RGPD'             => 'https://goo.gl/UpTLAK'                   ,
        'RGPDVideo'        => 'https://firebasestorage.googleapis.com/v0/b/redcrossquest-fr-dev.appspot.com/o/RCQ%2Fprotection_donnees_perso.mp4?alt=media&token=fdea365a-b909-4331-b839-c382549afb87',
        'graphPath'        => 'graph-display.html'                      ,
        'queteurDashboard' => 'Merci'                                   ,
        'email'            => [
          'sendgrid.api_key'    => getenv('SENDGRID_API_KEY'),
          'sendgrid.sender'     => getenv('SENDGRID_SENDER' ),
          'thanksMailBatchSize' => 10
        ],
      ],
    ],
  ]);
};
