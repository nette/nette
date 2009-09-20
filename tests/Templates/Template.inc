<?php

/**
 * Common code for Template test cases.
 */

/*use Nette\Loaders\LimitedScope;*/
/*use Nette\Templates\BaseTemplate;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\TemplateCacheStorage;*/



// Setup caching storage

class MockCacheStorage extends TemplateCacheStorage
{

	public function write($key, $data, array $dp)
	{
		file_put_contents(dirname(__FILE__) . '/compiled/' . preg_replace('#^.*[a-f0-9]{32}\.#', '', $key) . '.php', $data);
		return parent::write($key, $data, $dp);
	}

}



class MockTemplate extends BaseTemplate
{
	public $compiled;

	public function render($content = NULL)
	{
		$this->__set('template', $this);
		$this->compiled = $this->compile($content);
		LimitedScope::evaluate($this->compiled, $this->getParams());
	}

}

