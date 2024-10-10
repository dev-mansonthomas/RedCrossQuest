<?php
/** @noinspection SpellCheckingInspection */
return [
  'settings' => [
    'displayErrorDetails' => true, // set to false in production
    'online'              => true, //determine if secrets are fetch locally or from Google Secret Manager and if logging is done locally or
    // Renderer settings
    'renderer'            => [
      'template_path' => __DIR__ . '/../templates/',
    ],
    // logging settings
    'logger'              => [
      'name'      => 'RCQ' ,
      'level'     => 'info'
    ],
    'db'          => [
      'dsn'  => getenv('MYSQL_DSN'     ),
      'user' => getenv('MYSQL_USER'    )
    ],
    'PubSub'      => [
      'tronc_queteur_topic'        => 'tronc_queteur'       ,
      'tronc_queteur_update_topic' => 'tronc_queteur_update',
      'queteur_approval_topic'     => 'queteur_approval_topic'
    ],
    'jwt'         => [
      'issuer'   => getenv('JWT_ISSUER'     ),
      'audience' => getenv('JWT_ISSUER'     )
    ],
    'ReCaptcha'   => [
      'lowestAcceptableScore' => 0.7
    ],
    'appSettings' => [
      'sessionLength'    => 6                                         , //hours
      'appUrl'           => getenv('APP_URL')                ,
      'resetPwdPath'     => '#!/resetPassword?key='                   ,
      'deploymentType'   => getenv('APP_ENV')                ,   //D:Dev, T:Testing, P:Production,
      'RGPD'             => 'https://goo.gl/UpTLAK'                   ,
      'RGPDVideo'        => 'https://firebasestorage.googleapis.com/path_to_video',
      'graphPath'        => 'graph-display.html'                      ,
      'queteurDashboard' => 'Merci'                                   ,
      'email'            => [
        'sendgrid.sender'     => getenv('SENDGRID_SENDER' ),
        'thanksMailBatchSize' => 10
      ],
    ],
  ],
];
