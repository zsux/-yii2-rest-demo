<?php
Yii::setAlias('@user', __DIR__ . '/../modules/user');

$config = [
    'language' => 'zh-CN',
    'basePath' => __DIR__ . '/../',
    'vendorPath' => __DIR__ . '/../vendor',
    'runtimePath' =>  __DIR__ . '/../runtime',
    'modules' => [
        'user' => [
            'class' => 'user\Module',
        ],
    ],

    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'app\components\base\FileTarget',
                    'logVars' => [],
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/error.log',
                    'maxFileSize' => 51200,
                    'maxLogFiles' => 10,
                ],
                [
                    'class' => 'app\components\base\FileTarget',
                    'logVars' => [],
                    'levels' => ['info'],
                    'except' => ['yii\*'],
                    'logFile' => '@runtime/logs/info.log',
                    'maxFileSize' => 51200,
                    'maxLogFiles' => 10,
                ],
            ],
        ],

        'cache' => [
            'class' => 'yii\redis\Cache',
        ],

        'db_jsqb' => [
            'class' => 'yii\db\Connection',
            'dsn' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $yaml['DB_MYSQL']['default']['host'],
                $yaml['DB_MYSQL']['default']['port'],
                $yaml['DB_MYSQL']['default']['database']
            ),
            'username' => $yaml['DB_MYSQL']['default']['user'],
            'password' => $yaml['DB_MYSQL']['default']['password'],
            'charset' => 'utf8',
            'tablePrefix' => $yaml['DB_MYSQL']['default']['tablePrefix'],
            'attributes' => [
                \PDO::ATTR_TIMEOUT => $yaml['DB_MYSQL']['default']['timeout'],
            ],
        ],

        'db_rcm_ma' => [
            'class' => 'yii\db\Connection',
            'dsn' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $yaml['DB_MYSQL']['rcm_ma']['host'],
                $yaml['DB_MYSQL']['rcm_ma']['port'],
                $yaml['DB_MYSQL']['rcm_ma']['database']
            ),
            'username' => $yaml['DB_MYSQL']['rcm_ma']['user'],
            'password' => $yaml['DB_MYSQL']['rcm_ma']['password'],
            'charset' => 'utf8',
            'tablePrefix' => $yaml['DB_MYSQL']['rcm_ma']['tablePrefix'],
            'attributes' => [
                \PDO::ATTR_TIMEOUT => $yaml['DB_MYSQL']['rcm_ma']['timeout'],
            ],
        ],

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $yaml['DB_REDIS']['host'],
            'port' => $yaml['DB_REDIS']['port'],
            'password' => $yaml['DB_REDIS']['auth'] ?: null,
            'database' => 0,
        ],

        'memcache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => $yaml['DB_MEMCACHE']['default']['host'],
                    'port' => $yaml['DB_MEMCACHE']['default']['port'],
                    'weight' => 60,
                ],
            ],
        ],

        'verificationCode' => [
            'class' => 'app\components\utils\VerificationCode',
        ],

        'client' => [
            'class' => 'mike\client\Client',
            'remotes' => [
                'user' => [$yaml['URL_HTTP_API']['user']],
            ],

            'on beforeSend' => function (\mike\client\Request $request) {
                /* @var \mike\zipkin\Tracer $tracer */
                if ($tracer = Yii::$app->get('__tracer__', false)) {
                    $span = $tracer->createSpan(sprintf('%s:%s', $request->getRemote(), $request->getPath()));
                    $request->setHeaders(\mike\zipkin\Headers::createFromSapn($span)->toArray());
                    $get = $request->getQuery();
                    $span->addBinaryAnnotation('GET', $get ? http_build_query($get) : '');
                    $post = $request->getJson() ? $request->getJson() : $request->getFormParams();
                    $span->addBinaryAnnotation('POST', $post ? http_build_query($post) : '');
                    $span->start()->clientSend();
                    $request->setData('span', $span);
                }
            },

            'on afterRecv' => function (\mike\client\response\ResponseInterface $response) {
                /* @var \mike\zipkin\Span $span */
                if ($span = $response->getRequest()->getData('span')) {
                    $span->addBinaryAnnotation('RESPONSE', $response->getBody());
                    $span->clientRecv()->finish();
                }
            },
        ],
    ],
];

if (YII_DEBUG) {
    $config['components']['log']['targets'] = \yii\helpers\ArrayHelper::merge($config['components']['log']['targets'], [
        [
            'class' => 'app\components\base\FileTarget',
            'logVars' => [],
            'levels' => ['trace'],
            'logFile' => '@runtime/logs/trace.log',
            'maxFileSize' => 1024,
            'maxLogFiles' => 5,
        ],
        [
            'class' => 'app\components\base\FileTarget',
            'logVars' => [],
            'levels' => ['profile'],
            'logFile' => '@runtime/logs/profile.log',
            'maxFileSize' => 1024,
            'maxLogFiles' => 5,
        ],
    ]);
}

if (\yii\helpers\ArrayHelper::isIn(YII_ENV, ['local'])) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'mike\gii\Module',
    ];
}

return $config;
