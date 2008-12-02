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



/**
 * Lazy encapsulation of PresenterComponent::link().
 * Do not instantiate directly, use PresenterComponent::lazyLink()
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 */
class Link extends /*Nette\*/Object
{
	/** @var PresenterComponent */
	private $component;

	/** @var string */
	private $destination;

	/** @var array */
	private $args;


	/**
	 * Link specification.
	 * @param  PresenterComponent
	 * @param  string
	 * @param  array
	 */
	public function __construct(PresenterComponent $component, $destination, array $args = NULL)
	{
		$this->component = $component;
		$this->destination = $destination;
		$this->args = $args;
	}



	/**
	 * Returns link destination.
	 * @return string
	 */
	public function getDestination()
	{
		return $this->destination;
	}



	/**
	 * Changes link parameter.
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function setParam($key, $value)
	{
		$this->args[$key] = $value;
	}



	/**
	 * Returns link parameter.
	 * @param  string
	 * @return mixed
	 */
	public function getParam($key)
	{
		return isset($this->args[$key]) ? $this->args[$key] : NULL;
	}



	/**
	 * Converts link to URL.
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->component->link($this->destination, $this->args);

		} catch (/*\*/Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return '';
		}
	}

}
