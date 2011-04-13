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

use Nette;



/**
 * Template.
 *
 * @author     David Grudl
 */
abstract class Template extends Nette\Object implements ITemplate
{
	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var array of function(Template $sender); Occurs before a template is compiled - implement to customize the filters */
	public $onPrepareFilters = array();

	/** @var array */
	private $params = array();

	/** @var array compile-time filters */
	private $filters = array();

	/** @var array run-time helpers */
	private $helpers = array();

	/** @var array */
	private $helperLoaders = array();



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



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 * @abstract
	 */
	public function render()
	{
		throw new Nette\NotImplementedException;
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
		ob_start();
		try {
			$this->render();
			return ob_get_clean();

		} catch (\Exception $e) {
			ob_end_clean();
			if (func_num_args() && func_get_arg(0)) {
				throw $e;
			} else {
				Nette\Diagnostics\Debugger::toStringException($e);
			}
		}
	}



	/**
	 * Applies filters on template content.
	 * @param  string
	 * @return string
	 */
	public function compile($content)
	{
		if (!$this->filters) {
			$this->onPrepareFilters($this);
		}

		foreach ($this->filters as $filter) {
			$content = self::extractPhp($content, $blocks);
			$content = $filter/*5.2*->invoke*/($content);
			$content = strtr($content, $blocks); // put PHP code back
		}

		return self::optimizePhp($content);
	}



	/********************* template helpers ****************d*g**/



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
	public function setParams(array $params)
	{
		$this->params = $params;
		return $this;
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

				} elseif ($token[0] === T_OPEN_TAG && $token[1] === '<?' && isset($tokens[$n+1][1]) && $tokens[$n+1][1] === 'xml') {
					$php = & $res;
					$token[1] = '<<?php ?>?';

				} elseif ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO) {
					$res .= $id = "\x01@php:p" . count($blocks) . "@\x02";
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
	public static function optimizePhp($source)
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
