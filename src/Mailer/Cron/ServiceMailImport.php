<?php

namespace Mailer\Cron;

use Mailer\DI;
use Mailer\DAO\MailIdsDAO;
use Mailer\DAO\MailInfoDAO;
use Mailer\Parser\AvitoAdapter;
use Zend\Config\Config;
use Zend\Mail\Storage\Imap;
use Zend\Mail\Storage\Message;

class ServiceMailImport implements CronService
{
	private $mailConfig;

	/**
	 * @param array $options
	 */
	public function setup(array $options)
	{
		$di = DI::get();
		try {
			/** @var $config Config */
			$config = $di->getConfig()->mailbox;

			$this->mailConfig = [
				'host'     => $config->host,
				'port'     => $config->port,
				'ssl'      => $config->ssl,
				'user'     => $config->user,
				'password' => $config->password,
			];
		} catch (\Exception $e) {
			$di->getLogger()->err($e->getMessage());
			$this->mailConfig = [];
		}
	}

	/**
	 * @return boolean
	 */
	public function canRun()
	{
		$canRun = !empty($this->mailConfig);
		if (!$canRun) {
			DI::get()->getLogger()->err('Configure mailbox first!');
		}
		return $canRun;
	}

	/**
	 * @return string|null
	 */
	public function getLockName()
	{
		return 'MailImport';
	}

	/**
	 * @return string
	 */
	public function getHelp()
	{
		return "Script to read mails\n";
	}

	public function run()
	{
		$server = new Imap($this->mailConfig);
		$di = DI::get();
		$ids = [];

		foreach ($server as $key => $message) {
			$ids[] = $server->getUniqueId($key);
		}

		$config = $di->getConfig();
		$ids = $this->filterNotReadIds($ids, $config);

		foreach ($ids as $id) {
			try {
				$this->processMail($server, $id, $config);
			} catch (\Exception $e) {
				$di->getLogger()->error($e->getMessage(), [__CLASS__]);
			}
		}
	}

	private function processMail(Imap $server, $id, Config $config)
	{
		$mail = $server->getMessage($server->getNumberByUniqueId($id));
		$mailUniqId = $mail->messageId;

		$readMail = $this->createReadMail($id, $mailUniqId);

		$parser = new AvitoAdapter();
		$data = [];

		if ($content = $parser->checkMail($mail, $config)) {
			$data = $parser->process($content, $config);
		}

		$readMail
			->setDateRead(time())
			->save();

		$date = strtotime($mail->date);
		if (!empty($data)) {
			$mailInfo = MailInfoDAO::create();
			$mailInfo
				->setMailId($readMail->getId())
				->setEmail($data['email'])
				->setName($data['name'])
				->setCity($data['city'])
				->setSubjectId($data['subjectId'])
				->setComment($data['comment'])
				->setMessageDate($date)
				->setIsSent('false')
				->setFailCount(0);
			$mailInfo->save();

			DI::get()->getLogger()->info('Added new mail id='.$mailUniqId.', email='.$mailInfo->getEmail());
		}
	}

	private function createReadMail($id, $mailUniqId)
	{
		return MailIdsDAO::create()
			->setMailUniqId($mailUniqId)
			->setMailId($id);
	}

	private function filterNotReadIds(array $messageIds)
	{
		if (empty($messageIds)) {
			return $messageIds;
		}

		$foundIds = MailIdsDAO::create()->getNonReadMailIds($messageIds);

		return array_diff($messageIds, $foundIds);
	}
}
