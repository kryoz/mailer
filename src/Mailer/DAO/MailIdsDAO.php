<?php

namespace Mailer\DAO;

use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;

class MailIdsDAO extends DAOBase
{
	const MAIL_ID = 'mail_id';
	const MAIL_UNIQUE_ID = 'mail_uniq_id';
	const DATE_READ = 'date_read';

	public function __construct()
	{
		parent::__construct(
			[
				self::MAIL_ID,
				self::MAIL_UNIQUE_ID,
				self::DATE_READ,
			]
		);

		$this->dbTable = 'mail_ids';
	}

	public function getMailId()
	{
		return $this[self::MAIL_ID];
	}

	public function setMailId($emailId)
	{
		$this[self::MAIL_ID] = $emailId;
		return $this;
	}

	public function getMailUniqId()
	{
		return $this[self::MAIL_UNIQUE_ID];
	}

	public function setMailUniqId($emailId)
	{
		$this[self::MAIL_UNIQUE_ID] = $emailId;
		return $this;
	}

	public function getDateRead()
	{
		return $this[self::DATE_READ];
	}

	public function setDateRead($date)
	{
		$this[self::DATE_READ] = DbQueryHelper::timestamp2date($date);
		return $this;
	}

	public function getByMailId($mailId)
	{
		return $this->getByPropId(self::MAIL_ID, $mailId);
	}

	public function getNonReadMailIds(array $ids)
	{
		return $this->db->query(
			"SELECT mail_id FROM {$this->dbTable} WHERE mail_id IN (".DbQueryHelper::commaSeparatedHolders($ids).")",
			$ids,
			\PDO::FETCH_COLUMN
		);
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

