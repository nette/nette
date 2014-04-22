<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Templating;

use Nette,
	Nette\Caching,
	Nette\Utils\Callback,
	Latte;


/**
 * @deprecated
 */
class Template extends Nette\Object implements ITemplate
{
	/** @var array of function(Template $sender); Occurs before a template is compiled - implement to customize the filters */
	public $onPrepareFilters = array();

	/** @var string */
	private $latte;

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


	/**
	 * Sets template source code.
	 * @param  string
	 * @return self
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


	/********************* rendering ****************d*g**/


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if (!$this->filters) {
			$this->onPrepareFilters($this);
		}
		if ($latte = $this->getLatte()) {
			return $latte->setLoader(new Latte\Loaders\StringLoader)->render($this->source, $this->getParameters());
		}

		$cache = new Caching\Cache($storage = $this->getCacheStorage(), 'Nette.Template');
		$cached = $compiled = $cache->load($this->source);

		if ($compiled === NULL) {
			$compiled = $this->compile();
			$cache->save($this->source, $compiled, array(Caching\Cache::CONSTS => 'Nette\Framework::REVISION'));
			$cached = $cache->load($this->source);
		}

		$isFile = $cached !== NULL && $storage instanceof Caching\Storages\PhpFileStorage;
		self::load($isFile ? $cached['file'] : $compiled, $this->getParameters(), $isFile);
	}


	protected static function load(/*$code, $params, $isFile*/)
	{
		foreach (func_get_arg(1) as $__k => $__v) $$__k = $__v;
		unset($__k, $__v);
		if (func_get_arg(2)) {
			include func_get_arg(0);
		} else {
			$res = eval('?>' . func_get_arg(0));
			if ($res === FALSE && ($error = error_get_last()) && $error['type'] === E_PARSE) {
				throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
			}
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
	 * @param  can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		ob_start();
		try {
			$this->render();
			return ob_get_clean();

		} catch (\Exception $e) {
			ob_end_clean();
			if (func_num_args()) {
				throw $e;
			}
			trigger_error(sprintf('Exception in %s(): %s in %s:%i', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()), E_USER_ERROR);
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
			$code = call_user_func($filter, $code);
			$code = strtr($code, $blocks); // put PHP code back
		}

		if ($latte = $this->getLatte()) {
			return $latte->setLoader(new Latte\Loaders\StringLoader)->compile($code);
		}

		return Helpers::optimizePhp($code);
	}


	/********************* template filters & helpers ****************d*g**/


	public function setLatte(Latte\Engine $latte)
	{
		$this->latte = $latte;
	}


	/**
	 * @return Latte\Engine
	 */
	public function getLatte()
	{
		if (!$this->latte) {
			return NULL;
		}
		$latte = $this->latte instanceof Latte\Engine ? $this->latte : new Nette\Latte\Engine;

		foreach ($this->helpers as $key => $callback) {
			$latte->addFilter($key, $callback);
		}

		foreach ($this->helperLoaders as $callback) {
			$latte->addFilter(NULL, function($name) use ($callback, $latte) {
				if ($res = call_user_func($callback, $name)) {
					$latte->addFilter($name, $res);
				}
			});
		}

		if ($this->cacheStorage instanceof Nette\Caching\Storages\PhpFileStorage) {
			$latte->setTempDirectory($this->cacheStorage->getDir());
		}

		return $latte;
	}


	/**
	 * Registers callback as template compile-time filter.
	 * @param  callable
	 * @return self
	 */
	public function registerFilter($callback)
	{
		if ($callback instanceof Latte\Engine) { // back compatibility
			$this->latte = $callback;
		} elseif (is_array($callback) && $callback[0] instanceof Latte\Engine) {
			$this->latte = $callback[0];
		} elseif (strpos(Callback::toString($callback), 'Latte\Engine') !== FALSE) {
			$this->latte = TRUE;
		} elseif ($this->latte) {
			throw new Nette\DeprecatedException('Adding filters after Latte is not possible.');
		} else {
			$this->filters[] = Callback::check($callback);
		}
		return $this;
	}


	/**
	 * Returns all registered compile-time filters.
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}


	/**
	 * Registers callback as template run-time helper.
	 * @param  string
	 * @param  callable
	 * @return self
	 */
	public function registerHelper($name, $callback)
	{
		$this->helpers[strtolower($name)] = $callback;
		return $this;
	}


	/**
	 * Registers callback as template run-time helpers loader.
	 * @param  callable
	 * @return self
	 */
	public function registerHelperLoader($callback)
	{
		array_unshift($this->helperLoaders, $callback);
		return $this;
	}


	/**
	 * Returns all registered run-time helpers.
	 * @return array
	 */
	public function getHelpers()
	{
		return $this->helpers;
	}


	/**
	 * Returns all registered template run-time helper loaders.
	 * @return array
	 */
	public function getHelperLoaders()
	{
		return $this->helperLoaders;
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
				$helper = Callback::invoke($loader, $lname);
				if ($helper) {
					$this->registerHelper($lname, $helper);
					return Callback::invokeArgs($this->helpers[$lname], $args);
				}
			}
			return parent::__call($name, $args);
		}

		return Callback::invokeArgs($this->helpers[$lname], $args);
	}


	/**
	 * Sets translate adapter.
	 * @return self
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->registerHelper('translate', $translator === NULL ? NULL : array($translator, 'translate'));
		return $this;
	}


	/********************* template parameters ****************d*g**/


	/**
	 * Adds new template parameter.
	 * @return self
	 */
	public function add($name, $value)
	{
		if (array_key_exists($name, $this->params)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}

		$this->params[$name] = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 * @param  array
	 * @return self
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


	/**
	 * Sets a template parameter. Do not call directly.
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->params[$name] = $value;
	}


	/**
	 * Returns a template parameter. Do not call directly.
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		if (!array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist in template.", E_USER_NOTICE);
		}

		return $this->params[$name];
	}


	/**
	 * Determines whether parameter is defined. Do not call directly.
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
	 * @return self
	 */
	public function setCacheStorage(Caching\IStorage $storage)
	{
		$this->cacheStorage = $storage;
		return $this;
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
				if ($token[0] === T_INLINE_HTML) {
					$res .= $token[1];
					continue;

				} elseif ($token[0] === T_CLOSE_TAG) {
					if ($php !== $res) { // not <?xml
						$res .= str_repeat("\n", substr_count($php, "\n"));
					}
					$res .= $token[1];
					continue;

				} elseif ($token[0] === T_OPEN_TAG && $token[1] === '<?' && isset($tokens[$n+1][1]) && $tokens[$n+1][1] === 'xml') {
					$php = & $res;
					$token[1] = '<<?php ?>?';

				} elseif ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO) {
					$res .= $id = "<@php:p" . count($blocks) . "@";
					$php = & $blocks[$id];
				}
				$php .= $token[1];

			} else {
				$php .= $token;
			}
		}
		return $res;
	}

}
