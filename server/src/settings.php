<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level'=> Monolog\Logger::INFO     //TODO : fonctionne pas
        ],

        'db' => [
            'host'  => '127.0.0.1',
            'user'  => 'rcq',
            'pwd'   => 'rcq',
            'dbname'=> 'RCQ',

        ],
        'jwt' => [
          'secret'   => 'secret-rcq',
          'issuer'   => 'https://rcq.paquerette.com',
          'audience' => 'https://rcq.paquerette.com'
        ],
        'appSettings' => [
          'appUrl'        => 'http://localhost:3000/',
          'resetPwdPath'  => '#!/resetPassword?key='

        ],
      'email' => [
        'server'    => 'webmail.croix-rouge.fr',
        'port'      => 465,
        'from'      => 'thomas.manson@croix-rouge.fr',
        'username'  => 'mansont',
        'password'  => 'Volgograd8542++'
      ]
    ],
];
