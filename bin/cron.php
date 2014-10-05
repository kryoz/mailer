<?php

use Core\BaseException;
use Mailer\DI;
use Mailer\Cron\CronExecutor;
use Monolog\Logger;
use Mailer\DIBuilder;
use Zend\Config\Config;

set_error_handler(
    function ($code, $string, $errfile, $errline) {
        throw new BaseException($string, $code);
    }
    , E_ALL | E_STRICT
);

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */
$logger = $container->get('logger');
/* @var $logger Logger */

try {
	/* @var $cronExecutor CronExecutor */
	$cronExecutor = new CronExecutor;
	$cronExecutor->run();
} catch (Exception $e) {
	$logger->err($e);
	exit(1);
}