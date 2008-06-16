<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/ITemplate.php';



/**
 * Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class Template extends /*Nette::*/Object implements ITemplate
{
	/** @var string */
	public $root = '';

	/** @var bool */
	public $useCache = TRUE;

	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var string */
	private $fileName;

	/** @var string */
	private $currentFile;

	/** @var array */
	private $params = array();

	/** @var array */
	private $filters = array();

	/** @var Nette::Caching::Cache */
	private $cache;



	/**
	 * @param  string  default template file name (filepath = root + name)
	 * @return void
	 */
	public function setFile($fileName)
	{
		$this->fileName = $fileName;
	}



	/**
	 * @return string  default template file name
	 */
	public function getFile()
	{
		return $this->fileName;
	}



	/**
	 * @return string  current template file path
	 */
	public function getCurrentFile()
	{
		return $this->currentFile;
	}



	/**
	 * Adds new template parameter.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function add($name, $value)
	{
		if ($name === '') {
			throw new /*::*/InvalidArgumentException("The key must be a non-empty string.");
		}

		if (array_key_exists($name, $this->params)) {
			throw new /*::*/InvalidStateException("The variable '$name' exists yet.");
		}

		$this->params[$name] = $value;
	}



	/**
	 * Adds new template as parameter.
	 * @param  string  name
	 * @param  string|Template  file name or Template object
	 * @return Template
	 */
	public function addTemplate($name, $fileName)
	{
		if ($fileName instanceof self) {
			$this->add($name, $fileName);
			return $fileName;

		} else {
			$tpl = new self;
			$tpl->root = $this->root;
			$tpl->setFile($fileName);
			$tpl->params = & $this->params;
			$tpl->filters = & $this->filters;
			$this->add($name, $tpl);
			return $tpl;
		}
	}



	/**
	 * Registers callback as template filter.
	 * @param  callback
	 * @return void
	 */
	public function registerFilter($callback)
	{
		$this->filters[] = $callback;
	}



	/**
	 * Escapes string for use inside template.
	 * @param  string
	 * @return string
	 */
	public function escape($s)
	{
		if (is_string($s)) {
			return htmlSpecialChars($s, ENT_QUOTES);
		}
		return $s;
	}



	/**
	 * Translates and escapes string.
	 * @param  string
	 * @return string
	 */
	public function translate($s)
	{
		throw /*::*/NotImplementedException;
	}



	/**
	 * Renders template to output.
	 * @param  string  file name (optional)
	 * @param  array   parameters (optional)
	 * @return void
	 */
	public function render($fileName = NULL, $params = NULL)
	{
		if ($fileName === NULL) {
			$fileName = $this->fileName;

			if ($fileName === NULL) {
				throw new /*::*/InvalidStateException("Template file name was not specified.");
			}
		}

		if (substr($fileName, 0, 1) === '/') { // absolute vs. relative
			$filePath = $this->root . $fileName;

		} elseif ($this->currentFile === NULL) {
			$filePath = $this->root . '/' . $fileName;

		} else {
			$filePath = dirname($this->currentFile) . '/' . $fileName;
		}

		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new /*::*/FileNotFoundException("Missing template '$filePath'.");
		}

		if ($params === NULL) {
			$params = $this->params;
		}

		if (count($this->filters)) {
			$cache = $this->getCache();
			$cache->release();
			$key = md5($filePath) . '.' . $fileName;
			if (empty($cache[$key])) {
				$content = file_get_contents($filePath);
				foreach ($this->filters as $filter) {
					if ($filter instanceof /*Nette::*/Callback) {
						$content = $filter->invoke($this, $content);
					} else {
						$content = call_user_func($filter, $this, $content);
					}
				}
				$content = "<?php\n// template $filePath\n?>$content";
				$cache->save($key, $content, array('files' => $filePath));
			}

			$translated = $cache[$key]['file'];
			$handle = $cache[$key]['handle'];

		} else {
			$translated = $filePath;
		}

		$params['template'] = $this;

		$save = $this->currentFile;
		$this->currentFile = $filePath;

		self::_render($translated, $params);

		if (isset($handle)) fclose($handle);
		$this->currentFile = $save;
	}



	/**
	 * Renders template in limited scope.
	 * @param  string  file path
	 * @param  array   parameters
	 * @return void
	 */
	public static function _render(/*$file, $params*/)
	{
		extract(func_get_arg(1), EXTR_SKIP); // skip $this
		include func_get_arg(0);
	}



	/**
	 * Support for template rendering using 'echo $template'.
	 * @return string
	 */
	public function __toString()
	{
		try {
			ob_start();
			$this->render();
			return ob_get_clean();

		} catch (Exception $e) {
			return $e->__toString();
		}
	}



	/**
	 * @return Nette::Caching::Cache
	 */
	protected function getCache()
	{
		if ($this->cache === NULL) {
			if ($this->useCache) {
				$base = /*Nette::*/Environment::getVariable('cacheBase');
				$storage = new /*Nette::Caching::*/TemplateStorage($base);
			} else {
				$storage = new /*Nette::Caching::*/DummyStorage();
			}
			$this->cache = new /*Nette::Caching::*/Cache($storage, 'Nette.Template');
		}
		return $this->cache;
	}



	/**
	 * Sets a template parameter. Do not call directly.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	protected function __set($name, $value)
	{
		if ($name === '') {
			throw new /*::*/InvalidArgumentException("The key must be a non-empty string.");
		}

		$this->params[$name] = $value;
	}



	/**
	 * Returns a template parameter. Do not call directly.
	 * @param  string  name
	 * @return mixed  value
	 */
	protected function &__get($name)
	{
		if ($name === '') {
			throw new /*::*/InvalidArgumentException("The key must be a non-empty string.");
		}

		if ($this->warnOnUndefined && !array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist", E_USER_WARNING);
		}

		return $this->params[$name];
	}



	/**
	 * Determines whether parameter is defined. Do not call directly.
	 * @param  string    name
	 * @return boolean
	 */
	protected function __isset($name)
	{
		return isset($this->params[$name]);
	}



	/**
	 * Removes a template parameter. Do not call directly.
	 * @param  string    name
	 * @return void
	 */
	protected function __unset($name)
	{
		if ($name === '') {
			throw new /*::*/InvalidArgumentException("The key must be a non-empty string.");
		}

		unset($this->params[$name]);
	}

}
