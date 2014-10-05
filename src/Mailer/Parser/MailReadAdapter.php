<?php

namespace Mailer\Parser;

use Zend\Config\Config;
use Zend\Mail\Storage\Message;

interface MailReadAdapter
{
	public function process($content, Config $config);
} 