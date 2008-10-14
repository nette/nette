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
 * @package    Nette::Templates
 * @version    $Id$
 */

/*namespace Nette::Templates;*/

/*use Nette::Environment;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Templates/ITemplate.php';



/**
 * Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Templates
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

	/** @var array */
	private $helpers = array(
		'escape' => /*Nette::Templates::*/'TemplateHelpers::escape',
		'translate' => /*Nette::Templates::*/'TemplateHelpers::nop',
		'lower' => /*Nette::Templates::*/'TemplateHelpers::lower',
		'upper' => /*Nette::Templates::*/'TemplateHelpers::upper',
		'capitalize' => /*Nette::Templates::*/'TemplateHelpers::capitalize',
		'strip' => /*Nette::Templates::*/'TemplateHelpers::strip',
		'nl2br' => 'nl2br',
		'truncate' => /*Nette::*/'String::truncate',
		'bytes' => /*Nette::*/'String::bytes',
	);

	/** @var bool */
	private $isRendering;

	/** @var int */
	public static $cacheExpire;

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

		} elseif ($file == NULL) { // intentionally ==
			throw new /*::*/InvalidArgumentException("Template file name was not specified.");

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
		$this->filters[] = $callback;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return bool  return output instead of printing it?
	 * @return void|string
	 */
	public function render($return = FALSE)
	{
		if (isset(self::$livelock[$this->file])) {
			throw new /*::*/InvalidStateException("Circular rendering detected.");
		}

		list($content, $isFile) = $this->compile();

		try {
			self::$livelock[$this->file] = TRUE;
			$this->isRendering = TRUE;
			$res = NULL;
			if ($return) {
				ob_start();
			}
			TemplateFilters::phpEvaluation($this, $content, $isFile);
			if ($return) {
				$res = ob_get_clean();
			}

		} catch (Exception $e) {
			if ($return) {
				ob_end_clean();
			}
			// continue with shutting down
		} /* finally */ {
			unset(self::$livelock[$this->file]);
			$this->isRendering = FALSE;
			if (is_resource($isFile)) {
				fclose($isFile);
			}

			if (isset($e)) {
				throw $e;
			}

			return $res;
		}
	}



	/**
	 * Support for template rendering using 'echo $template'.
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->render(TRUE);

		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return '';
		}
	}



	/**
	 * Converts to SimpleXML.
	 * @return SimpleXMLElement
	 */
	public function toXml()
	{
		return simplexml_load_string('<xml>' . $this->render(TRUE) . '</xml>');
	}



	/**
	 * @return array (string, isFile/handle)
	 */
	private function compile()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new /*::*/InvalidStateException("Template file name was not specified.");
		}

		// strip fragment
		list($filePath) = explode('#', $this->file);

		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new /*::*/FileNotFoundException("Missing template file '$this->file'.");
		}

		if (!count($this->filters)) {
			return array($filePath, TRUE);
		}

		$content = $filePath;
		$isFile = TRUE;
		$cache = new /*Nette::Caching::*/Cache($this->getCacheStorage(), 'Nette.Template');
		$key = md5($this->file) . '.' . basename($this->file);
		$content = $cache[$key];

		if ($content === NULL) {
			$content = file_get_contents($filePath);
			$isFile = FALSE;

			reset($this->filters);
			while (list(, $filter) = each($this->filters)) {/**/
				if ($filter instanceof /*Nette::*/Callback) {
					$content = $filter->__invoke($this, $content);
				} else/**/ {
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
					/*Nette::Caching::*/Cache::FILES => $filePath,
					/*Nette::Caching::*/Cache::EXPIRE => self::$cacheExpire,
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
			throw new /*::*/InvalidStateException("The helper '$name' was not registered.");
		}

		return call_user_func_array($this->helpers[$name], $args);
	}



	/**
	 * Sets translate adapter.
	 * @param  Nette::ITranslator
	 * @return void
	 */
	public function setTranslator(/*Nette::*/ITranslator $translator = NULL)
	{
		$this->registerHelper('translate', $translator === NULL ? /*Nette::Templates::*/'TemplateHelpers::nop' : array($translator, 'translate'));
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
		if ($this->isRendering) {
			throw new /*::*/InvalidStateException("Parameters are read-only while rendering template.");
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
		if ($this->isRendering) {
			throw new /*::*/InvalidStateException("Parameters are read-only while rendering template.");
		}

		$this->params[$name] = $value;
	}



	/**
	 * Returns a template parameter. Do not call directly.
	 * @param  string  name
	 * @return mixed  value
	 */
	public function &__get($name)
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
		if ($this->isRendering) {
			throw new /*::*/InvalidStateException("Parameters are read-only while rendering template.");
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
			self::$cacheStorage = new TemplateStorage($base);

			if (self::$cacheExpire === NULL) {
				self::$cacheExpire = Environment::isLive() ? FALSE : 1;
			}
		}
		return self::$cacheStorage;
	}

}
