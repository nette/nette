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

/*use Nette::Environment;*/



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
	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var string */
	private $file;

	/** @var array */
	private $params = array();

	/** @var array */
	private $filters = array();

	/** @var int */
	private static $cacheExpire;

	/** @var Nette::Caching::ICacheStorage */
	private static $cacheStorage;

	/** @var array */
	private static $livelock = array();



	/**
	 * @param  string  template file path
	 * @return void
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}



	/**
	 * @return string  template file path
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * Creates subtemplate.
	 * @param  string  file name
	 * @param  array   parameters (optional)
	 * @return Template
	 */
	public function subTemplate($file, array $params = NULL)
	{
		if ($file instanceof self) {
			$tpl = $file;

		} else {
			$tpl = clone $this;
			if ($file == NULL) { // intentionally ==
				throw new /*::*/InvalidArgumentException("Template file name was not specified.");
			}
			if ($file[0] !== '/' && $file[1] !== ':') {
				$file = dirname($this->file) . '/' . $file;
			}
			$tpl->setFile($file);
		}

		if ($params === NULL) {
			$tpl->params = & $this->params;

		} else {
			foreach ($params as $key => $value) {
				if (is_int($key)) {
					$params[$value] = $this->params[$value];
				}
			}
			$tpl->params = & $params;
		}

		return $tpl;
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



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new /*::*/InvalidStateException("Template file name was not specified.");
		}

		if (isset(self::$livelock[$this->file])) {
			throw new /*::*/InvalidStateException("Circular rendering detected.");
		}

		// strip fragment
		list($filePath) = explode('#', $this->file);

		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new /*::*/FileNotFoundException("Missing template '$this->file'.");
		}

		$content = $filePath;
		$eval = FALSE;

		if (count($this->filters)) {
			$cache = new /*Nette::Caching::*/Cache($this->getCacheStorage(), 'Nette.Template');
			$content = NULL;

			$key = md5($this->file) . '.' . basename($this->file);
			$content = $cache[$key];

			if ($content === NULL) {
				$content = file_get_contents($filePath);
				$eval = TRUE;
				foreach ($this->filters as $filter) {
					if ($filter instanceof /*Nette::*/Callback) {
						$content = $filter->invoke($this, $content);
					} else {
						if (!is_callable($filter)) {
							throw new /*::*/InvalidStateException("Filter must be valid PHP callback or Nette::Callback object.");
						}
						$content = call_user_func($filter, $this, $content);
					}
				}
				$content = "<?php\n// template $this->file\n?>$content";
				$cache->save(
					$key,
					$content,
					array(
						'files' => $filePath,
						'expire' => self::$cacheExpire,
					)
				);
			}

			if (self::$cacheStorage instanceof /*Nette::Caching::*/TemplateStorage) {
				$cached = $cache[$key];
				if ($cached !== NULL) {
					$content = $cached['file'];
					$handle = $cached['handle'];
					$eval = FALSE;
				}
			}
		}

		$params = $this->params;
		$params['template'] = $this;

		try {
			self::$livelock[$this->file] = TRUE;
			self::_render($content, $params, $eval);

		} catch (Exception $e) {
			// continue with shutting down
		} /* finally */ {
			unset(self::$livelock[$this->file]);
			if (isset($handle)) fclose($handle);

			if (isset($e)) throw $e;
		}
	}



	/**
	 * Renders template in limited scope.
	 * @param  string  file path
	 * @param  array   parameters
	 * @param  bool    eval or include?
	 * @return void
	 */
	private static function _render(/*$file, $params, $eval*/)
	{
		extract(func_get_arg(1), EXTR_SKIP); // skip $this
		if (func_get_arg(2)) {
			eval('?>' . func_get_arg(0));
		} else {
			include func_get_arg(0);
		}
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



	/********************* template parameters ****************d*g**/



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
	public function addTemplate($name, $file)
	{
		$tpl = $this->subTemplate($file);
		$this->add($name, $tpl);
		return $tpl;
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



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette::Caching::Cache
	 * @return void
	 */
	public static function setCacheStorage(/*Nette::Caching::*/ICacheStorage $storage)
	{
		self::$cacheStorage = $storage;
	}



	/**
	 * @return Nette::Caching::ICacheStorage
	 */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			$base = Environment::getVariable('cacheBase');
			self::$cacheStorage = new /*Nette::Caching::*/TemplateStorage($base);

			if (self::$cacheExpire === NULL) {
				self::$cacheExpire = Environment::getName() === Environment::DEVELOPMENT ? 1 : FALSE;
			}
		}
		return self::$cacheStorage;
	}

}
