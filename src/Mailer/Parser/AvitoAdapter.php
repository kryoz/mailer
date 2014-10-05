<?php

namespace Mailer\Parser;

use Mailer\DI;
use Monolog\Logger;
use Zend\Config\Config;
use Zend\Mail\Storage\Message;
use Zend\Mail\Storage\Exception\InvalidArgumentException;

class AvitoAdapter implements MailReadAdapter
{
	public function process($content, Config $config)
	{
		$logger = DI::get()->getLogger();
		$data = [];

		$content = mb_convert_encoding($content, 'UTF-8', 'ASCII');
		$content = quoted_printable_decode($content);

		$data += $this->parseEmailAndName($content, $logger);
		$data += $this->parseCityAndSubjectId($content, $logger);
		$data += $this->parseComment($content, $logger);

		return $data;
	}

	public function checkMail(Message $mail, Config $globalConfig)
	{
		$config = $globalConfig->parser;

		$from = $mail->from;

		if (mb_strpos($from, $config->from) === false) {
			return;
		}

		try {
			$contentTypeHeader = $mail->getHeaderField('Content-Type', null);

			if ($contentTypeHeader[0] != 'text/html') {
				return;
			}
		} catch (InvalidArgumentException $e) {
			return;
		}

		return $mail->getContent();
	}

	private function parseEmailAndName($content, Logger $logger)
	{
		$hasFound = preg_match('~([^<>]*) &nbsp;/&nbsp; <a href="mailto:(\S+)"(?:.*?)>(?:.+)</a>~uis', $content, $matches);

		if (!$hasFound) {
			$logger->error('Mail body didn\'t contain email address or user name!');
			return [];
		}

		$data['name'] = mb_convert_case(html_entity_decode(trim($matches[1])), MB_CASE_TITLE);
		$data['email'] = $matches[2];

		return $data;
	}

	private function parseCityAndSubjectId($content, Logger $logger)
	{
		$hasFound = preg_match('~Новый отклик на Вашу вакансию <a(?:.+?)>(?:.+?) (\S+)(?: \((\S+)\))?</a>~uis', $content, $matches);

		if (!$hasFound) {
			$logger->error('Mail body didn\'t contain city or subject id!');
			return [];
		}

		$data['city'] = $matches[1];
		$data['subjectId'] = isset($matches[2]) ? $matches[2] : '';

		return $data;
	}

	private function parseComment($content, Logger $logger)
	{
		$hasFound = preg_match('~<td valign="top" style="word-wrap:break-word;">(.+?)</td>~uis', $content, $matches);

		if (!$hasFound) {
			$logger->error('Mail body didn\'t contain any comment!');
			return [];
		}

		$data['comment'] = trim(strip_tags(html_entity_decode($matches[1])));

		return $data;
	}
}