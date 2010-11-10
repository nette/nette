<?php

/**
 * Common code for Template test cases.
 */

use Nette\Loaders\LimitedScope,
	Nette\Templates\Template,
	Nette\Templates\TemplateCacheStorage;



// Setup caching storage

class MockCacheStorage extends TemplateCacheStorage
{

	public function write($key, $data, array $dp)
	{
		file_put_contents(__DIR__ . '/compiled/' . trim(str_replace(DIRECTORY_SEPARATOR, '.', $this->hint), '.') . '.php', preg_replace('#[a-f0-9]{10,}#', '...', $data));
		return parent::write($key, $data, $dp);
	}

}



class MockTemplate extends Template
{
	public $compiled;

	public function render($content = NULL)
	{
		$this->__set('template', $this);
		$this->compiled = $this->compile($content);
		ob_start();
		LimitedScope::evaluate($this->compiled, $this->getParams());
		return ob_get_clean();
	}

}
