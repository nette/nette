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
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Templates/ITemplate.php';



/**
 * Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
abstract class BaseTemplate extends /*Nette\*/Object implements ITemplate
{
	/** @var bool */
	public $warnOnUndefined = TRUE;

	/** @var array of function(BaseTemplate $sender); Occurs before a template is compiled - implement to customize the filters */
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
		/**/fixCallback($callback);/**/
		if (!is_callable($callback)) {
			$able = is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidArgumentException("Filter '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
		}
		if (in_array($callback, $this->filters)) {
			is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidStateException("Filter '$textual' was registered twice.");
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
	 * Applies filters on template content.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	protected function compile($content, $label = NULL)
	{
		if (!$this->filters) {
			$this->onPrepareFilters($this);
		}

		try {
			foreach ($this->filters as $filter) {
				$content = self::extractPhp($content, $blocks);
				$content = call_user_func($filter, $content);
				$content = strtr($content, $blocks); // put PHP code back
			}
		} catch (Exception $e) {
			is_callable($filter, TRUE, $textual);
			throw new /*\*/InvalidStateException("Filter $textual: " . $e->getMessage() . ($label ? " (in $label)" : ''), 0, $e);
		}

		if ($label) {
			$content = "<?php\n// $label\n//\n?>$content";
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
		/**/fixCallback($callback);/**/
		if (!is_callable($callback)) {
			$able = is_callable($callback, TRUE, $textual);
			throw new /*\*/InvalidArgumentException("Helper handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
		}
		$this->helpers[strtolower($name)] = $callback;
	}



	/**
	 * Registers callback as template run-time helpers loader.
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
				$helper = call_user_func($loader, $lname);
				if ($helper) {
					$this->registerHelper($lname, $helper);
					return call_user_func_array($helper, $args);
				}
			}
			return parent::__call($name, $args);
		}

		return call_user_func_array($this->helpers[$lname], $args);
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
	 * Sets all parameters.
	 * @param  array
	 * @return void
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
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
		foreach (token_get_all($source) as $token) {
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
		return $res;
	}



	/**
	 * Removes unnecessary blocks of PHP code.
	 * @param  string
	 * @return string
	*/
	private static function optimizePhp($source)
	{
		$res = $php = '';
		$tokens = token_get_all($source);
		$iterator = new /*Nette\*/SmartCachingIterator(token_get_all($source));
		foreach ($iterator as $token) {
			if (is_array($token)) {
				if ($token[0] === T_INLINE_HTML) {
					$res .= $token[1];

				} elseif ($token[0] === T_CLOSE_TAG) {
					$next = $iterator->getNextValue();
					if (substr($res, -1) !== '<' && preg_match('#^<\?php\s*$#', $php)) {
						$php = ''; // removes empty (?php ?), but retains ((?php ?)?php

					} elseif (is_array($next) && $next[0] === T_OPEN_TAG) { // remove ?)(?php
						$ch = substr(rtrim($php), -1);
						if ($ch !== ';' && $ch !== '{' && $ch !== '}' && $ch !== ':' && $ch !== '/') $php .= ';';
						if (substr($next[1], -1) === "\n") $php .= "\n";
						$iterator->next();

					} else {
						$res .= preg_replace('#;?(\s)*$#', '$1', $php) . $token[1]; // remove last semicolon before ?)
						$php = '';
					}
				} else {
					$php .= $token[1];
				}
			} else {
				$php .= $token;
			}
		}
		return $res . $php;
	}

}
