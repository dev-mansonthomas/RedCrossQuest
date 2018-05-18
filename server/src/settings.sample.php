<?php

$mysqlUser    = getenv('MYSQL_USER'     );
$mysqlP       = getenv('MYSQL_PASSWORD' );
$mysqlDsn     = getenv('MYSQL_DSN'      );

if($mysqlUser == "")
{
  $mysqlUser = 'rcq';
}
if($mysqlP == "")
{
  $mysqlP = 'rcq';
}
if($mysqlDsn == "")
{
  $mysqlDsn = 'mysql:host=127.0.0.1;port=3306;dbname=RCQ;charset=utf8;';
}

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'RCQ',
            'path' => __DIR__ . '/../logs/app.log',
            'level'=> 'info',
            'max_files' => 10
        ],

        'db' => [
          'dsn'      => $mysqlDsn ,
          'user'     => $mysqlUser,
          'pwd'      => $mysqlP

        ],
        'jwt' => [
          'secret'        => 'secret-rcq',
          'issuer'        => 'https://rcq.server.com',
          'audience'      => 'https://rcq.server.com'
        ],
        'appSettings' => [
          'sessionLength'   => 6                       ,  // hours
          'appUrl'          => 'http://localhost:3000/',
          'resetPwdPath'    => '#!/resetPassword?key=' ,
          'deploymentType'  => 'D'                     ,   //D:Dev, T:Testing, P:Production,
          'gmapAPIKey'      => 'google maps api key'
        ],
      'email' => [
        'server'    => 'smtp.server.com',
        'port'      => 465,
        'from'      => 'email@server.com',
        'username'  => 'login',
        'password'  => 'password'
      ]
    ],
];
