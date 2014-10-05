<?php

namespace Mailer\Cron;

use Mailer\DI;
use Core\Utils\CMDUtils;
use Mailer\Locker\AlreadyLockedException;
use Mailer\Locker\LockerInDB;

class CronExecutor
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function run()
	{
		$options = CMDUtils::getOptionsList();
		if (!($service = $this->getService($options))) {
			return $this->printHelp();
		}

		$service->setup($options);
		if (!$service->canRun() || isset($options['--help'])) {
			return $this->printServiceHelp($service);
		}

		$locker = new LockerInDB();
		$lockName = $this->getLockName($service);
		if (isset($options['--unlock'])) {
			$locker->unlock($lockName);
			$this->msg("Service was unlocked. You can run it now.");
			return;
		}
		try {
			$locker->lock($lockName);
		} catch (AlreadyLockedException $e) {
			if (!isset($options['--hiddenLock'])) {
				$this->msg("Service already locked, ".$e->getMessage());
			}
			return;
		}

		try {
			$service->run();
			$locker->unlock($this->getLockName($service));
		} catch (\Exception $e) {
			$locker->unlock($this->getLockName($service));
			DI::get()->getLogger()->error($e->getMessage());
			exit(1);
		}
	}

	/**
	 * @param $options
	 * @return CronService
	 */
	private function getService($options)
	{
		if (!isset($options['--s'])) {
			return;
		}

		/* @var $cronEnum CronEnum */
		$cronEnum = CronEnum::create($options['--s']);
		return $cronEnum->getServiceInstance();
	}

	private function printHelp()
	{
		$this->msg("Known cron services:");
		foreach (array_keys(CronEnum::getNameList()) as $name) {
			$this->msg("--s=" . $name);
		}
	}

	private function printServiceHelp(CronService $service)
	{
		print $service->getHelp();
	}

	private function msg($msg)
	{
		print $msg."\n";
	}

	private function getLockName(CronService $cronService)
	{
		return $cronService->getLockName() ?: get_class($cronService);
	}
}
