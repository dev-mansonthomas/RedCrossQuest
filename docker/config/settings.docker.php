<?php
/**
 * RedCrossQuest - settings file for the Docker local environment.
 * Copied to server/src/settings.php by run.sh / Makefile when it doesn't
 * already exist, so it never overwrites a developer's hand-tuned file.
 *
 * Keep in sync with server/src/settings.sample.php when upstream changes.
 */
return [
    'settings' => [
        'displayErrorDetails' => true,
        'online'              => false,
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        'logger' => [
            'name'  => 'RCQ',
            'level' => 'debug',
        ],
        'db' => [
            'dsn'  => getenv('MYSQL_DSN'),
            'user' => getenv('MYSQL_USER'),
        ],
        'PubSub' => [
            'tronc_queteur_topic'        => 'tronc_queteur',
            'tronc_queteur_update_topic' => 'tronc_queteur_update',
            'queteur_approval_topic'     => 'queteur_approval_topic',
        ],
        'jwt' => [
            'issuer'   => getenv('JWT_ISSUER') ?: 'rcq-local',
            'audience' => getenv('JWT_ISSUER') ?: 'rcq-local',
        ],
        'ReCaptcha' => [
            'lowestAcceptableScore' => 0.0,
        ],
        'appSettings' => [
            'sessionLength'    => 6,
            'appUrl'           => getenv('APP_URL') ?: 'http://localhost:3000/',
            'resetPwdPath'     => '#!/resetPassword?key=',
            'deploymentType'   => getenv('APP_ENV') ?: 'D',
            'RGPD'             => 'https://goo.gl/UpTLAK',
            'RGPDVideo'        => 'https://firebasestorage.googleapis.com/path_to_video',
            'graphPath'        => 'graph-display.html',
            'queteurDashboard' => 'Merci',
            'email' => [
                'sendgrid.sender'     => getenv('SENDGRID_SENDER') ?: 'noreply@localhost',
                'thanksMailBatchSize' => 10,
            ],
        ],
    ],
];
