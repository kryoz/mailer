<?php

namespace Mailer\Cron;

use Mailer\DI;
use Core\Enum\Enum;

class CronEnum extends Enum
{
	protected static $names = array(
		'mailImport' => ServiceMailImport::class,
		'mailSender' => ServiceMessageSender::class,
	);

	public function getServiceInstance()
	{
		$service = DI::get()->spawn($this->getName());

		if (!$service instanceof CronService) {
			throw new \Exception("Expects {$this->getName()} implements CronService interface");
		}
		return $service;
	}
}
