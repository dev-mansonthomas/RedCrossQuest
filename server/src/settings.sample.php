<?php
return [
  'settings' => [
    'displayErrorDetails' => true, // set to false in production
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
      'user' => getenv('MYSQL_USER'    ),
      'pwd'  => getenv('MYSQL_PASSWORD')
    ],
    'PubSub'      => [
      'tronc_queteur_topic'        => 'tronc_queteur'       ,
      'tronc_queteur_update_topic' => 'tronc_queteur_update'
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
      'appUrl'           => getenv('APP_URL')                ,
      'resetPwdPath'     => '#!/resetPassword?key='                   ,
      'deploymentType'   => getenv('APP_ENV')                ,   //D:Dev, T:Testing, P:Production,
      'gmapAPIKey'       => getenv('GOOGLE_MAPS_API')        ,
      'RGPD'             => 'https://goo.gl/UpTLAK'                   ,
      'graphPath'        => 'graph-display.html'                      ,
      'queteurDashboard' => 'Merci'                                   ,
      'email'            => [
        'sendgrid.api_key'    => getenv('SENDGRID_API_KEY'),
        'sendgrid.sender'     => getenv('SENDGRID_SENDER' ),
        'thanksMailBatchSize' => 10
      ],
    ],
  ],
];