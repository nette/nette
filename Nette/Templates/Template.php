<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/

/*use Nette\Environment;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Templates/ITemplate.php';



/**
 * Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Templates
 */
class Template extends /*Nette\*/Object implements ITemplate
{
	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var string */
	private $file;

	/** @var array */
	private $params = array();

	/** @var array */
	private $filters = array();

	/** @var array */
	private $helpers = array();

	/** @var string  pre-rendered content */
	private $content;

	/** @var int */
	public static $cacheExpire = FALSE;

	/** @var Nette\Caching\ICacheStorage */
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

		} elseif ($file == NULL) { // intentionally ==
			throw new /*\*/InvalidArgumentException("Template file name was not specified.");

		} else {
			$tpl = clone $this;
			if ($file[0] !== '/' && $file[1] !== ':') {
				$file = dirname($this->file) . '/' . $file;
			}
			$tpl->setFile($file);
		}

		if ($params === NULL) {
			$tpl->params = & $this->params;

		} else {
			$tpl->params = & $params;
			$tpl->params += $this->params;
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
		/**/fixCallback($callback);/**/
		$this->filters[] = $callback;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->content !== NULL) {
			echo $this->content;
			return;
		}

		if (isset(self::$livelock[$this->file])) {
			throw new /*\*/InvalidStateException("Circular rendering detected.");
		}

		list($content, $isFile) = $this->compile();

		self::$livelock[$this->file] = TRUE;

		try {
			$this->params['template'] = $this;
			if ($isFile) {
				/*Nette\Loaders\*/LimitedScope::load($content, $this->params);
			} else {
				/*Nette\Loaders\*/LimitedScope::evaluate($content, $this->params);
			}
		} catch (/*\*/Exception $e) {
			// continue with shutting down
		}

		unset(self::$livelock[$this->file]);
		if (is_resource($isFile)) {
			fclose($isFile);
		}

		if (isset($e)) {
			throw $e;
		}
	}



	/**
	 * Renders template to string.
	 * @return void
	 */
	public function preRender()
	{
		$this->content = $this->__toString(TRUE);
	}



	/**
	 * Renders template to string.
	 * @return string
	 */
	public function __toString()
	{
		ob_start();
		try {
			$this->render();
			return ob_get_clean();

		} catch (/*\*/Exception $e) {
			ob_end_clean();
			if (func_get_args()) {
				throw $e;
			} else {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return '';
			}
		}
	}



	/**
	 * Converts to SimpleXML. (experimental)
	 * @return SimpleXMLElement
	 */
	public function toXml()
	{
		$dom = new DOMDocument;
		$dom->loadHTML('<html><meta http-equiv="Content-Type" content="text/html;charset=utf-8">' . str_replace("\r", '', $this->__toString()) . '</html>');
		return simplexml_import_dom($dom)->body;
		//return simplexml_load_string('<xml>' . $this->__toString() . '</xml>');
	}



	/**
	 * @return array (string, isFile/handle)
	 */
	private function compile()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new /*\*/InvalidStateException("Template file name was not specified.");
		}

		// strip fragment
		list($filePath) = explode('#', $this->file);

		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new /*\*/FileNotFoundException("Missing template file '$this->file'.");
		}

		if (!count($this->filters)) {
			return array($filePath, TRUE);
		}

		$isFile = TRUE;
		$cache = new /*Nette\Caching\*/Cache($this->getCacheStorage(), 'Nette.Template');
		$key = md5($this->file) . '.' . basename($this->file);
		$content = $cache[$key];

		if ($content === NULL) {
			$content = file_get_contents($filePath);
			$isFile = FALSE;

			foreach ($this->filters as $filter) {
				if (!is_callable($filter)) {
					throw new /*\*/InvalidStateException("Filter must be valid PHP callback object.");
				}

				// remove PHP code
				$res = '';
				$blocks = array();
				unset($php);
				foreach (token_get_all($content) as $token) {
					if (is_array($token)) {
						if ($token[0] === T_INLINE_HTML) {
							$res .= $token[1];
							unset($php);
						} else {
							if (!isset($php)) {
								$res .= $php = '<php:p' . count($blocks) . '/>';
								$php = & $blocks[$php];
							}
							$php .= $token[1];
						}
					} else {
						$php .= $token;
					}
				}

				$content = call_user_func($filter, $res, $this->file);
				$content = strtr($content, $blocks); // put PHP code back
			}

			$content = "<?php\n// template $this->file\n?>$content";
			$cache->save(
				$key,
				$content,
				array(
					/*Nette\Caching\*/Cache::FILES => $filePath,
					/*Nette\Caching\*/Cache::EXPIRE => self::$cacheExpire,
				)
			);
		}

		if (self::$cacheStorage instanceof TemplateStorage) {
			$cached = $cache[$key];
			if ($cached !== NULL) {
				return array($cached['file'], $cached['handle']);
			}
		}

		return array($content, $isFile);
	}



	/********************* template helpers ****************d*g**/



	/**
	 * Registers callback as template helper.
	 * @param  string
	 * @param  callback
	 * @return void
	 */
	public function registerHelper($name, $callback)
	{
		/**/fixCallback($callback);/**/
		$this->helpers[$name] = $callback;
	}



	/**
	 * Call a template helper. Do not call directly.
	 * @param  string  helper name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (!isset($this->helpers[$name])) {
			throw new /*\*/InvalidStateException("The helper '$name' was not registered.");
		}

		return call_user_func_array($this->helpers[$name], $args);
	}



	/**
	 * Sets translate adapter.
	 * @param  Nette\ITranslator
	 * @return void
	 */
	public function setTranslator(/*Nette\*/ITranslator $translator = NULL)
	{
		$this->registerHelper('translate', $translator === NULL ? NULL : array($translator, 'translate'));
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
		if (array_key_exists($name, $this->params)) {
			throw new /*\*/InvalidStateException("The variable '$name' exists yet.");
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
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}



	/**
	 * Sets a template parameter. Do not call directly.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->params[$name] = $value;
	}



	/**
	 * Returns a template parameter. Do not call directly.
	 * @param  string  name
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		if ($this->warnOnUndefined && !array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist in template '$this->file'", E_USER_WARNING);
		}

		return $this->params[$name];
	}



	/**
	 * Determines whether parameter is defined. Do not call directly.
	 * @param  string    name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->params[$name]);
	}



	/**
	 * Removes a template parameter. Do not call directly.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->params[$name]);
	}



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	public static function setCacheStorage(/*Nette\Caching\*/ICacheStorage $storage)
	{
		self::$cacheStorage = $storage;
	}



	/**
	 * @return Nette\Caching\ICacheStorage
	 */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			$base = Environment::getVariable('cacheBase');
			self::$cacheStorage = new TemplateStorage($base);
		}
		return self::$cacheStorage;
	}

}
