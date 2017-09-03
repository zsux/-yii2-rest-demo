<?php

$yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../config/yaml/config.yml'));

$config = [
    'id' => 'speed-purse',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'class' => 'app\components\base\Request',
            'cookieValidationKey' => '742a043f8eae6a519da0345e990cf2d9',
            'enableCsrfValidation' => false,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'errorHandler' => [
            'class' => 'app\components\base\ErrorHandler',
        ],

        'response' => [
            'on beforeSend' => function () {
                /* @var \mike\zipkin\Tracer $tracer */
                if ($tracer = Yii::$app->get('__tracer__', false)) {
                    /* @var \mike\zipkin\Span $span */
                    if ($span = Yii::$app->get('__top_span__', false)) {
                        $span->serverSend()->finish();
                        Yii::$app->response->getHeaders()->add('tid', $tracer->getTraceId());
                        Yii::$app->response->on('afterSend', function () use ($tracer) {
                            try {
                                $tracer->flush();
                            } catch (\Exception $e) {
                                Yii::error($e->getMessage());
                            }
                        });
                    }
                }
            },
            'on afterSend' => function () {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
            },
        ],
    ],

    /*'on beforeRequest' => function () use ($yaml) {
        $headers = \mike\zipkin\Headers::createFromHttp();
        $tracer = new \mike\zipkin\Tracer(Yii::$app->id, $headers->getTraceId());
        if (YII_ENV != 'prod') {
            $httpLogger = new \mike\zipkin\transport\HttpLogger($yaml['URL_ZIPKIN']);
            $tracer->setLogger($httpLogger);
        } else {
            $file = Yii::getAlias('@runtime') . '/zipkin/trace.log';
            $fileLogger = new \mike\zipkin\transport\FileLogger($file, 51200);
            $tracer->setLogger($fileLogger);
        }

        Yii::$app->set('__tracer__', $tracer);
        list($route) = Yii::$app->request->resolve();
        $span = $tracer->createSpan($route, $headers->getSpanId());
        Yii::$app->set('__top_span__', $span);
        $span->addBinaryAnnotation('GET', http_build_query(Yii::$app->request->getQueryParams()));
        $span->addBinaryAnnotation('POST', http_build_query(Yii::$app->request->getBodyParams()));
        $span->start()->serverRecv();
    },*/
];

$config['params'] = array_merge(require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php'));

$config = \yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/web-local.php'),
    require(__DIR__ . '/common.php'),
    require(__DIR__ . '/common-local.php')
);

if (\yii\helpers\ArrayHelper::isIn(YII_ENV, ['local', 'dev', 'test'])) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['modules']['swagger'] = [
        'class' => 'mike\swagger\Module',
    ];
}

return $config;
