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
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * JavaScript output console.
 *
 * <code>
 * $js = new JavaScriptConsole;
 * $js->jQuery('table tr:eq(2) img')
 * 		->css('z-index', 1000)
 * 		->animate(array('top' => '100px'));
 *
 * $js->fifteen->move(5, 6);
 *
 * $js->fifteen->partialId = '';
 * $js->flush();
 * </code>
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Web
 */
class JavaScriptConsole extends /*Nette\*/Object
{
	/** @var array */
	private $out = array();



	/**
	 * @return void
	 */
	public function flush()
	{
		echo implode(";\n", $this->out) . ";\n";
		$this->out = array();
	}



	/**
	 * Sets value of a JavaScript property.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$js = new JavaScript('', $this->out[]);
		$js->__set($name, $value);
	}



	/**
	 * Returns JavaScript property value.
	 * @param  string  property name
	 * @return JavaScript
	 */
	public function &__get($name)
	{
		$js = new JavaScript('', $this->out[]);
		return $js->__get($name);
	}



	/**
	 * Calls JavaScript function.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return JavaScript
	 */
	public function __call($method, $args)
	{
		$js = new JavaScript('', $this->out[]);
		return $js->__call($method, $args);
	}



	/**
	 * Appends user expressions.
	 * @param  mixed  one or more parameters
	 * @return JavaScript
	 */
	public function raw($arg)
	{
		$args = func_get_args();
		return call_user_func_array(array(new JavaScript('', $this->out[]), 'raw'), $args);
	}

}
