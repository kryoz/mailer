<?php

namespace Mailer\Cron;

use Mailer\DI;
use Mailer\DAO\MailIdsDAO;
use Mailer\DAO\MailInfoDAO;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Message as MimeMessage;
use Mailer\Template\TemplateEngine;
use Monolog\Logger;
use Zend\Config\Config;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Part;

class ServiceMessageSender implements CronService
{
	private $mailId;
	private $to;

	/**
	 * @param array $options
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function setup(array $options)
	{
		if (isset($options['--id']) && is_string($mailId = $options['--id'])) {
			$this->mailId = $mailId;
		}

		if (isset($options['--to']) && is_string($to = $options['--to'])) {
			$this->to = $to;
		}
	}

	/**
	 * @return boolean
	 */
	public function canRun()
	{
		return true; //always can run
	}

	/**
	 * @return string|null
	 */
	public function getLockName()
	{
		return 'MessageSender';
	}

	/**
	 * @return string
	 */
	public function getHelp()
	{
		return "Service to process mail messages queue
		Options:
		--id=<mailId> - sends only specified mail with id
		--to=<email> - sends email to <email>";
	}

	public function run()
	{
		if ($this->mailId) {
			$mail = MailIdsDAO::create()->getById($this->mailId);
			if ($mail->getId()) {
				$mailInfo = MailInfoDAO::create()->getByMailId($mail->getId());
				$this->sendEmail($mailInfo);
			}
			return;
		}

		foreach (MailInfoDAO::create()->getPendingList() as $mailInfo) {
			$this->sendEmail($mailInfo);
		}
	}

	private function sendEmail(MailInfoDAO $mailInfo)
	{
		$config = DI::get()->getConfig();
		$logger = DI::get()->getLogger();

		if ($text = $this->renderMessageBody($mailInfo, $config, $logger)) {
			if ($config->sender->useSMTP) {
				$smtpConfig = $config->sender;
				$smtpOptions = new SmtpOptions([
					'name' => $smtpConfig->name,
					'host' => $smtpConfig->host,
					'connection_class' => $smtpConfig->connection_class,
					'port' => $smtpConfig->port,
					'connection_config' => [
						'ssl' => $smtpConfig->ssl,
						'username' => $smtpConfig->username,
						'password' => $smtpConfig->password,
					]
				]);
				$transport = new Smtp();
				$transport->setOptions($smtpOptions);
			} else {
				$transport = new Sendmail();
			}

			try {
				$logger->info('Preparing email to '.$mailInfo->getEmail().' (#'.$mailInfo->getId().')', ['Mail sender']);

				$html = new Part($text);
				$html->type = "text/html";

				$body = new MimeMessage();
				$body->setParts([$html]);

				$mail = new Message();
				$mail
					->setEncoding('utf-8')
					->setFrom($config->sender->from)
					->setTo($this->to ?: $mailInfo->getEmail())
					->setSubject($config->sender->subject)
					->setBody($body);

				$transport->send($mail);

				$mailInfo->setIsSent('true');

				$logger->info('Email to '.$mailInfo->getEmail().' is sent (#'.$mailInfo->getId().')', ['Mail sender']);
			} catch (\Exception $e) {
				$logger->alert('Mailer failure! '.$e->getMessage(), ['Mail sender']);
				$mailInfo
					->setFailCount($mailInfo->getFailCount()+1)
					->setIsSent('false');
			}

			$mailInfo->save();
		}
	}

	/**
	 * @param MailInfoDAO $mailInfo
	 * @param $config
	 * @param $logger
	 * @return string
	 */
	private function renderMessageBody(MailInfoDAO $mailInfo, Config $config, Logger $logger)
	{
		try {
			$templater = new TemplateEngine();
			$message = $templater
				->setTemplateFile(ROOT . DIRECTORY_SEPARATOR . $config->sender->templateFile)
				->setVars(
					[
						'name' => $mailInfo->getName(),
						'email' => $mailInfo->getEmail(),
						'city' => $mailInfo->getCity(),
						'subjectId' => $mailInfo->getSubjectId()
					]
				)
				->render();

		} catch (\Exception $e) {
			$logger->alert('Possibly template render error: ' . $e->getMessage(), ['Mail sender']);
			$mailInfo->setFailCount($mailInfo->getFailCount() + 1);
			return null;
		}

		return $message;
	}
}
