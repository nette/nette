<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Templates/IFileTemplate.php';



/**
 * Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
class Template extends /*Nette\*/Object implements IFileTemplate
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

	/** @var array */
	private $helperLoaders = array();

	/** @var int */
	public static $cacheExpire = FALSE;

	/** @var Nette\Caching\ICacheStorage */
	private static $cacheStorage;



	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return void
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}



	/**
	 * Returns the path to the template file.
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
		if (in_array($callback, $this->filters, TRUE)) {
			is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidStateException("Filter '$textual' was registered twice.");
		}
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
			throw new /*\*/InvalidStateException("Template file name was not specified.");

		} elseif (!is_file($this->file) || !is_readable($this->file)) {
			throw new /*\*/FileNotFoundException("Missing template file '$this->file'.");
		}

		$this->params['template'] = $this;

		if (!count($this->filters)) {
			/*Nette\Loaders\*/LimitedScope::load($this->file, $this->params);
			return;
		}

		$cache = new /*Nette\Caching\*/Cache($this->getCacheStorage(), 'Nette.Template');
		$key = md5($this->file) . count($this->filters) . '.' . basename($this->file);
		$cached = $content = $cache[$key];

		if ($content === NULL) {
			$content = file_get_contents($this->file);

			foreach ($this->filters as $filter) {
				if (!is_callable($filter)) {
					$able = is_callable($filter, TRUE, $textual);
					throw new /*\*/InvalidStateException("Filter '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
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
								$res .= $php = "\x01@php:p" . count($blocks) . "@\x02";
								$php = & $blocks[$php];
							}
							$php .= $token[1];
						}
					} else {
						$php .= $token;
					}
				}

				try {
					$content = call_user_func($filter, $res);
				} catch (Exception $e) {
					is_callable($filter, TRUE, $textual);
					throw new /*\*/InvalidStateException("Filter $textual: " . $e->getMessage() . " (in file $this->file)", 0, $e);
				}

				$content = strtr($content, $blocks); // put PHP code back
			}

			$content = "<?php\n// template $this->file\n?>$content";
			$cache->save(
				$key,
				$content,
				array(
					/*Nette\Caching\*/Cache::FILES => $this->file,
					/*Nette\Caching\*/Cache::EXPIRE => self::$cacheExpire,
				)
			);
			$cached = $cache[$key];
		}

		if ($cached !== NULL && self::$cacheStorage instanceof TemplateCacheStorage) {
			/*Nette\Loaders\*/LimitedScope::load($cached['file'], $this->params);
			fclose($cached['handle']);

		} else {
			/*Nette\Loaders\*/LimitedScope::evaluate($content, $this->params);
		}
	}



	/**
	 * Renders template to string.
	 * @param bool  can throw exceptions? (hidden parameter)
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
			if (func_num_args() && func_get_arg(0)) {
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
		if (!is_callable($callback)) {
			$able = is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidArgumentException("Helper handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
		}
		$this->helpers[strtolower($name)] = $callback;
	}



	/**
	 * Registers callback as template helpers loader.
	 * @param  callback
	 * @return void
	 */
	public function registerHelperLoader($callback)
	{
		/**/fixCallback($callback);/**/
		if (!is_callable($callback)) {
			$able = is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidArgumentException("Helper loader '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
		}
		$this->helperLoaders[] = $callback;
	}



	/**
	 * Call a template helper. Do not call directly.
	 * @param  string  helper name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$name = strtolower($name);
		if (!isset($this->helpers[$name])) {
			foreach ($this->helperLoaders as $loader) {
				$helper = call_user_func($loader, $name);
				if ($helper) {
					$this->registerHelper($name, $helper);
					return call_user_func_array($helper, $args);
				}
			}
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
			trigger_error("The variable '$name' does not exist in template '$this->file'", E_USER_NOTICE);
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
			self::$cacheStorage = new TemplateCacheStorage(/*Nette\*/Environment::getVariable('cacheBase'));
		}
		return self::$cacheStorage;
	}

}
