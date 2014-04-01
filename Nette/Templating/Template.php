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
class Template extends Latte\Template implements ITemplate
{
	/** @var array of function(Template $sender); Occurs before a template is compiled - implement to customize the filters */
	public $onPrepareFilters = array();

	/** @var string */
	private $source;

	/** @var array compile-time filters */
	private $preFilters = array();

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
			trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}


	/**
	 * Applies filters on template content.
	 * @return string
	 */
	public function compile()
	{
		if (!$this->preFilters) {
			$this->onPrepareFilters($this);
		}

		$code = $this->getSource();
		foreach ($this->preFilters as $filter) {
			$code = self::extractPhp($code, $blocks);
			$code = call_user_func($filter, $code);
			$code = strtr($code, $blocks); // put PHP code back
		}

		return Latte\Helpers::optimizePhp($code);
	}


	/**
	 * @internal
	 */
	public function renderChildTemplate($name, array $params = array())
	{
		if ($this instanceof Nette\Templating\IFileTemplate) {
			if (!preg_match('#/|[a-z]:#iA', $name)) {
				$name = dirname($this->getFile()) . '/' . $name;
			}
			$tpl = clone $this;
			$tpl->setFile($name);
			$tpl->setParameters($params);
			$tpl->render();
		} else {
			parent::renderChildTemplate($name, $params);
		}
	}


	/********************* template filters & helpers ****************d*g**/


	/**
	 * Registers callback as template compile-time filter.
	 * @param  callable
	 * @return self
	 */
	public function registerFilter($callback)
	{
		$this->preFilters[] = Callback::check($callback);
		return $this;
	}


	/**
	 * Registers callback as template run-time helper.
	 * @param  string
	 * @param  callable
	 * @return self
	 */
	public function registerHelper($name, $callback)
	{
		$this->filters[strtolower($name)] = $callback;
		return $this;
	}


	/**
	 * Registers callback as template run-time helpers loader.
	 * @param  callable
	 * @return self
	 */
	public function registerHelperLoader($callback)
	{
		$filters = & $this->filters;
		array_unshift($filters[NULL], function($name) use ($callback, & $filters) {
			if ($res = call_user_func($callback, $name)) {
				$filters[$name] = $res;
			}
		});
		return $this;
	}


	/**
	 * Returns all registered run-time helpers.
	 * @return array
	 */
	public function getHelpers()
	{
		return $this->filters;
	}


	/**
	 * Returns all registered template run-time helper loaders.
	 * @return array
	 */
	public function getHelperLoaders()
	{
		return $this->filters[NULL];
	}


	/**
	 * Sets translate adapter.
	 * @return self
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->filters['translate'] = $translator === NULL ? NULL : array($translator, 'translate');
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
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParameters()
	{
		$this->params['template'] = $this;
		return $this->params;
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
