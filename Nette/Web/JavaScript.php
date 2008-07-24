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
 * @package    Nette::Web
 * @version    $Id$
 */

/*namespace Nette::Web;*/



/**
 * PHP to JavaScript helper.
 *
 * usage:
 *     $js = new JavaScript;
 *     $js->jQuery('.prod img')
 *        ->css('position', 'relative')
 *        ->animate(array('top' => '100px'))
 *     echo $js;
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class JavaScript
{
	/** @var string */
	protected $js;

	/** @var string|FALSE */
	private $state = '';


	public function __construct($init = '', & $ref = NULL)
	{
		$this->js = & $ref;
		$this->js = $init;
	}



	/**
	 * Sets value of a JavaScript property.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ($this->state === FALSE) {
			throw new /*::*/InvalidStateException('Invalid syntax.');

		} elseif ($value instanceof self) {
			$this->js .= $this->state . $name . ' = ' . $value->js;

		} else {
			$this->js .= $this->state . $name . ' = ' . json_encode($value);
		}

		$this->state = FALSE;
	}



	/**
	 * Returns JavaScript property value.
	 * @param  string  property name
	 * @return JavaScript provides a fluent interface
	 */
	public function &__get($name)
	{
		if ($this->state === FALSE) {
			throw new /*::*/InvalidStateException('Invalid syntax.');

		} elseif ($name === 'var') {
			$this->js .= $this->state . $name . ' ';

		} else {
			$this->js .= $this->state . $name;
			$this->state = '.';
		}

		return $this;
	}



	/**
	 * Calls JavaScript function.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return JavaScript  provides a fluent interface
	 */
	public function __call($method, $args)
	{
		if ($this->state === FALSE) {
			throw new /*::*/InvalidStateException('Invalid syntax.');
		}
		foreach ($args as $key => $arg) {
			$args[$key] = $arg instanceof self ? $arg->js : json_encode($arg);
		}
		$this->js .= $this->state . $method . '(' . implode(', ', $args) . ')';
		$this->state = '.';
		return $this;
	}



	/**
	 * Appends user expressions.
	 * @param  mixed  one or more parameters
	 * @return JavaScript provides a fluent interface
	 */
	public function raw($arg)
	{
		$this->state = '';
		foreach (func_get_args() as $arg) {
			$this->js .= is_string($arg) ? $arg : json_encode($arg);
		}
		return $this;
	}



	/**
	 * Returns string represenation of JavaScript expression.
	 * @return string
	 */
	public function __toString()
	{
		return $this->js;
	}

}
