<?php
require_once 'vendor/autoload.php';

$log = new Monolog\Logger('debug');
$log->pushHandler(new \Monolog\Handler\ErrorLogHandler());
$cache = new \Doctrine\Common\Cache\FilesystemCache(__DIR__.'/cache');
$api = new \QBNK\QBank\API\QBankApi(
	'v3.qbank.se',
	new \QBNK\QBank\API\Credentials('f780f6e4f2a77527fc9e03d7e226d495bdbc7a84', 'system', 'wizuseteta45'),
	['log' => $log, 'cache' => $cache, 'cachePolicy' => new \QBNK\QBank\API\CachePolicy(\QBNK\QBank\API\CachePolicy::EVERYTHING, 3600)]);
var_dump($api->media()->retrieveMedia(1555)->getName());