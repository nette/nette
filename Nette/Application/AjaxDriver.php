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
 * @package    Nette\Application
 * @version    $Id$
 */

/*namespace Nette\Application;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/IAjaxDriver.php';



/**
 * AJAX output strategy.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 */
class AjaxDriver extends /*Nette\*/Object implements IAjaxDriver
{
	/** @var array */
	private $data = array();

	/** @var Nette\Web\IHttpResponse */
	private $httpResponse;



	/**
	 * Generates link.
	 * @param  string
	 * @return string
	 */
	public function link($url)
	{
		return "return !nette.action(" . ($url === NULL ? "this.href" : json_encode($url)) . ", this)";
	}



	/**
	 * @param  Nette\Web\IHttpResponse
	 * @return void
	 */
	public function open(/*Nette\Web\*/IHttpResponse $httpResponse)
	{
		$httpResponse->expire(FALSE);
		$this->httpResponse = $httpResponse;
	}



	/**
	 * @return void
	 */
	public function close()
	{
		$this->httpResponse->setContentType('application/x-javascript', 'utf-8');
		echo json_encode($this->data);
		$this->data = array();
	}



	/********************* AJAX response ****************d*g**/



	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function fireEvent($event, $arg)
	{
		$args = func_get_args();
		array_shift($args);
		$this->data['events'][] = array('event' => $event, 'args' => $args);
	}



	/**
	 * Sets a response parameter. Do not call directly.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}



	/**
	 * Returns a response parameter. Do not call directly.
	 * @param  string  name
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		if ($name === '') {
			throw new /*\*/InvalidArgumentException("The key must be a non-empty string.");
		}

		return $this->data[$name];
	}



	/**
	 * Determines whether parameter is defined. Do not call directly.
	 * @param  string    name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}



	/**
	 * Removes a response parameter. Do not call directly.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->data[$name]);
	}

}
