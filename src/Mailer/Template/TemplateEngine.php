<?php

namespace Mailer\Template;

use Mailer\DI;

class TemplateEngine
{
	protected $file;
	protected $vars;

	public function setTemplateFile($file)
	{
		if (!file_exists($file)) {
			throw new \Exception('Invalid template file specified '.$file);
		}
		$this->file = $file;
		return $this;
	}

	public function setVars($vars)
	{
		$this->vars = $vars;
		return $this;
	}

	public function render()
	{
		extract($this->vars);
		ob_start();
        try {
            require $this->file;
        } catch (\Exception $e) {
            DI::get()->getLogger()->err($e->getMessage(), [__CLASS__]);
        }

		$text = ob_get_contents();
		ob_end_clean();

		return $text;
	}
} 