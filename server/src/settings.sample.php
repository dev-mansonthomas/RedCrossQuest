<?php
return [
  'settings' => [
    'displayErrorDetails' => true, // set to false in production
    // Renderer settings
    'renderer'            => [
      'template_path' => __DIR__ . '/../templates/',
    ],
    // Monolog settings
    'logger'              => [
      'name'      => 'RCQ'                        ,
      'path'      => __DIR__ . '/../logs/app.log' ,
      'level'     => 'info'                       ,
      'max_files' => 10
    ],
    'gcp' => [
      'projectId' => 'redcrossquest-fr-test',
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
      'secret'   => 'secret',
      'issuer'   => 'https://rcq.host.com',
      'audience' => 'https://rcq.host.com'
    ],
    'ReCaptcha'   => [
      'secret'                => 'secret',
      'lowestAcceptableScore' => 0.7
    ],
    'appSettings' => [
      'sessionLength'    => 6                                         , //hours
      'appUrl'           => 'https://rcq.host.com/'         ,
      'resetPwdPath'     => '#!/resetPassword?key='                   ,
      'deploymentType'   => 'T'                                       ,   //D:Dev, T:Testing, P:Production,
      'gmapAPIKey'       => 'secret' ,
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