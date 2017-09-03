<?php

$yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../config/yaml/config.yml'));

$config = [
    'id' => 'speed-purse-console',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [],
    'params' => [],
];

$config['params'] = array_merge(require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php'));

$config = \yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/console-local.php'),
    require(__DIR__ . '/common.php'),
    require(__DIR__ . '/common-local.php')
);

return $config;
