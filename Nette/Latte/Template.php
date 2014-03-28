<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette,
	Nette\Utils\Callback;


/**
 * Template.
 *
 * @author     David Grudl
 */
class Template extends Nette\Object
{
	/** @var Engine */
	private $engine;

	/** @var Engine */
	private $name;

	/** @var array */
	protected $params = array();

	/** @var array run-time filters */
	protected $filters = array(
		NULL => array(), // dynamic
	);


	public function __construct(array $params, array & $filters, Engine $engine, $name)
	{
		$params['template'] = $this;
		$this->setParameters($params);
		$this->filters = & $filters;
		$this->engine = $engine;
		$this->name = $name;
	}


	/**
	 * @return Engine
	 */
	public function getEngine()
	{
		return $this->engine;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Renders template.
	 * @return void
	 */
	public function renderChildTemplate($name, array $params = array())
	{
		$name = $this->engine->getLoader()->getChildName($name, $this->name);
		$this->engine->render($name, $params);
	}


	/**
	 * Call a template run-time filter. Do not call directly.
	 * @param  string  filter name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$lname = strtolower($name);
		if (!isset($this->filters[$lname])) {
			$args2 = $args;
			array_unshift($args2, $lname);
			foreach ($this->filters[NULL] as $filter) {
				$res = Callback::invokeArgs($filter, $args2);
				if ($res !== NULL) {
					return $res;
				} elseif (isset($this->filters[$lname])) {
					return Callback::invokeArgs($this->filters[$lname], $args);
				}
			}
			return parent::__call($name, $args);
		}
		return Callback::invokeArgs($this->filters[$lname], $args);
	}


	/********************* template parameters ****************d*g**/


	/**
	 * Sets all parameters.
	 * @param  array
	 * @return self
	 */
	public function setParameters(array $params)
	{
		$this->params = $params;
		$this->params['template'] = $this;
		return $this;
	}


	/**
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParameters()
	{
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

}
