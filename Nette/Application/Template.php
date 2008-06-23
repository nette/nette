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
	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var string */
	private $file;

	/** @var array */
	private $params = array();

	/** @var array */
	private $filters = array();

	/** @var Nette::Caching::Cache */
	private $cache = FALSE;



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
		if ($file instanceof self) {
			$this->add($name, $file);
			return $file;

		} else {
			$tpl = clone $this;
			$this->absolutize($file);
			$tpl->setFile($file);
			$tpl->params = & $this->params;
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
	public function render($file = NULL, array $params = NULL)
	{
		if ($file !== NULL) {
			if ($file instanceof self) {
				return $file->render();
			}

			$tpl = clone $this;
			$this->absolutize($file);
			$tpl->setFile($file);
			if ($params !== NULL) {
				foreach ($params as $key => $value) {
					if (is_int($key)) {
						$params[$value] = $this->params[$value];
					}
				}
				$tpl->params = & $params;
			}
			return $tpl->render();
		}


		if ($this->file === NULL) {
			throw new /*::*/InvalidStateException("Template file name was not specified.");
		}

		// strip fragment
		list($filePath) = explode('#', $this->file);

		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new /*::*/FileNotFoundException("Missing template '$this->file'.");
		}

		$content = $filePath;
		$eval = FALSE;

		if (count($this->filters)) {
			$cache = $this->getCache();
			$content = NULL;

			if ($cache) {
				$key = md5($this->file) . '.' . basename($this->file);
				$content = $cache[$key];
			}

			if ($content === NULL) {
				$content = file_get_contents($filePath);
				$eval = TRUE;
				foreach ($this->filters as $filter) {
					if ($filter instanceof /*Nette::*/Callback) {
						$content = $filter->invoke($this, $content);
					} else {
						if (!is_callable($filter)) {
							throw new /*::*/InvalidArgumentException("Filter must be valid PHP callback or Nette::Callback object.");
						}
						$content = call_user_func($filter, $this, $content);
					}
				}
				if ($cache) {
					$content = "<?php\n// template $this->file\n?>$content";
					$cache->save($key, $content, array('files' => $filePath));
				}
			}

			if ($cache && $cache->getStorage() instanceof /*Nette::Caching::*/TemplateStorage) {
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

		self::_render($content, $params, $eval);

		if (isset($handle)) fclose($handle);
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
	 * Set cache storage.
	 * @param  Nette::Caching::Cache|NULL
	 * @return void
	 */
	public function setCache(/*Nette::Caching::*/Cache $cache = NULL)
	{
		$this->cache = $cache;
	}



	/**
	 * @return Nette::Caching::Cache|NULL
	 */
	public function getCache()
	{
		if ($this->cache === NULL) {
			return NULL;

		} elseif ($this->cache === FALSE) {
			// lazy init
			$base = /*Nette::*/Environment::getVariable('cacheBase');
			$storage = new /*Nette::Caching::*/TemplateStorage($base);
			return $this->cache = new /*Nette::Caching::*/Cache($storage, 'Nette.Template');

		} else {
			return $this->cache;
		}
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



	/**
	 * @param  string
	 * @return void
	 */
	private function absolutize(& $file)
	{
		if ($file[0] !== '/' && $file[1] !== ':') {
			$file = dirname($this->file) . '/' . $file;
		}
	}

}
