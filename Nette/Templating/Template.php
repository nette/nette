<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;

use Nette,
	Nette\Caching;



/**
 * Template.
 *
 * @author     David Grudl
 */
class Template extends Nette\Object implements ITemplate
{
	const PHASE_INITIALIZING = 1;
	const PHASE_USER_SPACE = 2;

	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var array of function(Template $sender); Occurs before a template is compiled - implement to customize the filters */
	public $onPrepareFilters = array();

	/** @var array list of properties which are reserved for framework and shouldn't be overridden */
	public $reservedProperties = array('control', 'presenter', 'user', 'baseUri', 'basePath', 'flashes', 'nette.*');

	/** @var string */
	private $source;

	/** @var array */
	private $params = array();

	/** @var array compile-time filters */
	private $filters = array();

	/** @var array run-time helpers */
	private $helpers = array();

	/** @var array */
	private $helperLoaders = array();

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var int */
	private $phase = self::PHASE_INITIALIZING;


	/**
	 * Sets template source code.
	 * @param  string
	 * @return Template  provides a fluent interface
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}



	/**
	 * Returns template source code.
	 * @return source
	 */
	public function getSource()
	{
		return $this->source;
	}


	/**
	 * @return int
	 */
	public function getPhase()
	{
		return $this->phase;
	}


	/**
	 * Move template to next phase
	 * @param int $phase
	 * @return Template  provides a fluent interface
	 */
	public function setPhase($phase)
	{
		if (!is_numeric($phase) || $phase < $this->phase) throw new \Nette\InvalidArgumentException("Cannot revert to earlier phase");
		$this->phase = $phase;
		return $this;
	}


	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		$cache = new Caching\Cache($storage = $this->getCacheStorage(), 'Nette.Template');
		$cached = $compiled = $cache->load($this->source);

		if ($compiled === NULL) {
			$compiled = $this->compile();
			$cache->save($this->source, $compiled, array(Caching\Cache::CONSTS => 'Nette\Framework::REVISION'));
			$cached = $cache->load($this->source);
		}

		if ($cached !== NULL && $storage instanceof Caching\Storages\PhpFileStorage) {
			Nette\Utils\LimitedScope::load($cached['file'], $this->getParameters());
		} else {
			Nette\Utils\LimitedScope::evaluate($compiled, $this->getParameters());
		}
	}



	/**
	 * Renders template to file.
	 * @param  string
	 * @return void
	 */
	public function save($file)
	{
		if (file_put_contents($file, $this->__toString(TRUE)) === FALSE) {
			throw new Nette\IOException("Unable to save file '$file'.");
		}
	}



	/**
	 * Renders template to string.
	 * @param  bool  can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		$args = func_get_args();
		ob_start();
		try {
			$this->render();
			return ob_get_clean();

		} catch (\Exception $e) {
			ob_end_clean();
			if ($args && $args[0]) {
				throw $e;
			} else {
				Nette\Diagnostics\Debugger::toStringException($e);
			}
		}
	}



	/**
	 * Applies filters on template content.
	 * @return string
	 */
	public function compile()
	{
		if (!$this->filters) {
			$this->onPrepareFilters($this);
		}

		$code = $this->getSource();
		foreach ($this->filters as $filter) {
			$code = self::extractPhp($code, $blocks);
			$code = $filter/*5.2*->invoke*/($code);
			$code = strtr($code, $blocks); // put PHP code back
		}

		return self::optimizePhp($code);
	}



	/********************* template filters & helpers ****************d*g**/



	/**
	 * Registers callback as template compile-time filter.
	 * @param  callback
	 * @return void
	 */
	public function registerFilter($callback)
	{
		$callback = callback($callback);
		if (in_array($callback, $this->filters)) {
			throw new Nette\InvalidStateException("Filter '$callback' was registered twice.");
		}
		$this->filters[] = $callback;
	}



	/**
	 * Returns all registered compile-time filters.
	 * @return array
	 */
	final public function getFilters()
	{
		return $this->filters;
	}



	/**
	 * Registers callback as template run-time helper.
	 * @param  string
	 * @param  callback
	 * @return void
	 */
	public function registerHelper($name, $callback)
	{
		$this->helpers[strtolower($name)] = callback($callback);
	}



	/**
	 * Registers callback as template run-time helpers loader.
	 * @param  callback
	 * @return void
	 */
	public function registerHelperLoader($callback)
	{
		$this->helperLoaders[] = callback($callback);
	}



	/**
	 * Returns all registered run-time helpers.
	 * @return array
	 */
	final public function getHelpers()
	{
		return $this->helpers;
	}



	/**
	 * Call a template run-time helper. Do not call directly.
	 * @param  string  helper name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$lname = strtolower($name);
		if (!isset($this->helpers[$lname])) {
			foreach ($this->helperLoaders as $loader) {
				$helper = $loader/*5.2*->invoke*/($lname);
				if ($helper) {
					$this->registerHelper($lname, $helper);
					return $this->helpers[$lname]->invokeArgs($args);
				}
			}
			return parent::__call($name, $args);
		}

		return $this->helpers[$lname]->invokeArgs($args);
	}



	/**
	 * Sets translate adapter.
	 * @param  Nette\Localization\ITranslator
	 * @return Template  provides a fluent interface
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->registerHelper('translate', $translator === NULL ? NULL : array($translator, 'translate'));
		return $this;
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
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}

		$this->params[$name] = $value;
	}



	/**
	 * Sets all parameters.
	 * @param  array
	 * @return Template  provides a fluent interface
	 */
	public function setParameters(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}



	/**
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParameters()
	{
		$this->params['template'] = $this;
		return $this->params;
	}



	/** @deprecated */
	function setParams(array $params)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setParameters() instead.', E_USER_WARNING);
		return $this->setParameters($params);
	}



	/** @deprecated */
	function getParams()
	{
		trigger_error(__METHOD__ . '() is deprecated; use getParameters() instead.', E_USER_WARNING);
		return $this->getParameters();
	}



	/**
	 * Sets a template parameter. Do not call directly.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ($this->phase >= self::PHASE_USER_SPACE && $this->isReservedParameter($name)) {
			trigger_error("Overriding reserved parameter '$name' in a template", E_USER_NOTICE);
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
		if ($this->warnOnUndefined && !array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist in template.", E_USER_NOTICE);
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


	/**
	 * Check whether given property name matches one of reserved property names
	 * @param string $name
	 * @return bool
	 */
	protected function isReservedParameter($name)
	{
		if (in_array($name, $this->reservedProperties)) return TRUE;
		foreach ($this->reservedProperties as $reservedName) {
			if(\Nette\Utils\Strings::match($name, "\x01^" . $reservedName . "$\x01")) return TRUE;
		}
		return FALSE;
	}

	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	public function setCacheStorage(Caching\IStorage $storage)
	{
		$this->cacheStorage = $storage;
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public function getCacheStorage()
	{
		if ($this->cacheStorage === NULL) {
			return new Caching\Storages\DevNullStorage;
		}
		return $this->cacheStorage;
	}



	/********************* tools ****************d*g**/



	/**
	 * Extracts all blocks of PHP code.
	 * @param  string
	 * @param  array
	 * @return string
	 */
	private static function extractPhp($source, & $blocks)
	{
		$res = '';
		$blocks = array();
		$tokens = token_get_all($source);
		foreach ($tokens as $n => $token) {
			if (is_array($token)) {
				if ($token[0] === T_INLINE_HTML || $token[0] === T_CLOSE_TAG) {
					$res .= $token[1];
					continue;

				} elseif ($token[0] === T_OPEN_TAG && $token[1] === '<?' && isset($tokens[$n+1][1]) && $tokens[$n+1][1] === 'xml') {
					$php = & $res;
					$token[1] = '<<?php ?>?';

				} elseif ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO) {
					$res .= $id = "<?php \x01@php:p" . count($blocks) . "@\x02";
					$php = & $blocks[$id];
				}
				$php .= $token[1];

			} else {
				$php .= $token;
			}
		}
		return $res;
	}



	/**
	 * Removes unnecessary blocks of PHP code.
	 * @param  string
	 * @return string
	 */
	public static function optimizePhp($source, $lineLength = 80, $existenceOfThisParameterSolvesDamnBugInPHP535 = NULL)
	{
		$res = $php = '';
		$lastChar = ';';
		$tokens = new \ArrayIterator(token_get_all($source));
		foreach ($tokens as $key => $token) {
			if (is_array($token)) {
				if ($token[0] === T_INLINE_HTML) {
					$lastChar = '';
					$res .= $token[1];

				} elseif ($token[0] === T_CLOSE_TAG) {
					$next = isset($tokens[$key + 1]) ? $tokens[$key + 1] : NULL;
					if (substr($res, -1) !== '<' && preg_match('#^<\?php\s*$#', $php)) {
						$php = ''; // removes empty (?php ?), but retains ((?php ?)?php

					} elseif (is_array($next) && $next[0] === T_OPEN_TAG) { // remove ?)(?php
						if (!strspn($lastChar, ';{}:/')) {
							$php .= $lastChar = ';';
						}
						if (substr($next[1], -1) === "\n") {
							$php .= "\n";
						}
						$tokens->next();

					} elseif ($next) {
						$res .= preg_replace('#;?(\s)*$#', '$1', $php) . $token[1]; // remove last semicolon before ?)
						if (strlen($res) - strrpos($res, "\n") > $lineLength
							&& (!is_array($next) || strpos($next[1], "\n") === FALSE)
						) {
							$res .= "\n";
						}
						$php = '';

					} else { // remove last ?)
						if (!strspn($lastChar, '};')) {
							$php .= ';';
						}
					}

				} elseif ($token[0] === T_ELSE || $token[0] === T_ELSEIF) {
					if ($tokens[$key + 1] === ':' && $lastChar === '}') {
						$php .= ';'; // semicolon needed in if(): ... if() ... else:
					}
					$lastChar = '';
					$php .= $token[1];

				} else {
					if (!in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG))) {
						$lastChar = '';
					}
					$php .= $token[1];
				}
			} else {
				$php .= $lastChar = $token;
			}
		}
		return $res . $php;
	}

}
