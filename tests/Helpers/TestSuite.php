<?php

namespace Tests\Helpers;

use Core\DI;
use Mailer\DIBuilder;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Orno\Di\Container;
use ReflectionClass;

class TestSuite extends \PHPUnit_Framework_TestCase
{
	protected $userSeq = 1;

	protected function setUp()
	{
		parent::setUpBeforeClass();
		$container = DI::get()->container();
		DIBuilder::setupConfig($container);
		$this->setupLogger($container);
		$container->add('db', null);
	}

	protected static function getMethod($class, $methodName) {
		$class = new ReflectionClass($class);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}

	private function setupLogger(Container $container)
	{
		$container->add(
			'logger',
			function () use ($container) {
				$logger = new Logger('CronService');
				$logger->pushHandler(new TestHandler());
				return $logger;
			},
			true
		);
	}
} 