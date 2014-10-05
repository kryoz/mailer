<?php

namespace Mailer\DAO;

use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;

class MailInfoDAO extends DAOBase
{
	const MAIL_ID = 'mail_id';
	const EMAIL = 'email';
	const NAME = 'name';
	const CITY = 'city';
	const SUBJECT_ID = 'subject_id';
	const COMMENT = 'comment';
	const MESSAGE_DATE = 'message_date';
	const IS_SENT = 'is_sent';
	const FAIL_COUNT = 'fail_count';

	protected $types = [
		self::IS_SENT => \PDO::PARAM_BOOL,
	];

	public function __construct()
	{
		parent::__construct(
			[
				self::MAIL_ID,
				self::EMAIL,
				self::NAME,
				self::CITY,
				self::SUBJECT_ID,
				self::COMMENT,
				self::IS_SENT,
				self::FAIL_COUNT,
				self::MESSAGE_DATE,
			]
		);

		$this->dbTable = 'mail_info';
	}

	public function getMailId()
	{
		return $this[self::MAIL_ID];
	}

	public function setMailId($id)
	{
		$this[self::MAIL_ID] = $id;
		return $this;
	}

	public function getEmail()
	{
		return $this[self::EMAIL];
	}

	public function setEmail($email)
	{
		$this[self::EMAIL] = $email;
		return $this;
	}

	public function getName()
	{
		return $this[self::NAME];
	}

	public function setName($name)
	{
		$this[self::NAME] = $name;
		return $this;
	}

	public function getCity()
	{
		return $this[self::CITY];
	}

	public function setCity($city)
	{
		$this[self::CITY] = $city;
		return $this;
	}

	public function getSubjectId()
	{
		return $this[self::SUBJECT_ID];
	}

	public function setSubjectId($subjectId)
	{
		$this[self::SUBJECT_ID] = $subjectId;
		return $this;
	}

	public function getComment()
	{
		return $this[self::COMMENT];
	}

	public function setComment($comment)
	{
		$this[self::COMMENT] = $comment;
		return $this;
	}

	public function getMessageDate()
	{
		return $this[self::MESSAGE_DATE];
	}

	public function setMessageDate($date)
	{
		$this[self::MESSAGE_DATE] = DbQueryHelper::timestamp2date($date);
		return $this;
	}

	public function getIsSent()
	{
		return $this[self::IS_SENT];
	}

	public function setIsSent($isSent)
	{
		$this[self::IS_SENT] = $isSent;
		return $this;
	}

	public function getFailCount()
	{
		return $this[self::FAIL_COUNT];
	}

	public function setFailCount($failCount)
	{
		$this[self::FAIL_COUNT] = $failCount;
		return $this;
	}

	public function getByMailId($mailId)
	{
		return $this->getByPropId(self::MAIL_ID, $mailId);
	}

	public function getPendingList()
	{
		return $this->getListByQuery("SELECT * FROM {$this->dbTable} WHERE is_sent IS FALSE AND fail_count <= 3");
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

