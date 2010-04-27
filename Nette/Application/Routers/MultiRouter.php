<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * The router broker.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class MultiRouter extends /*Nette\*/Object implements IRouter, /*\*/ArrayAccess, /*\*/Countable, /*\*/IteratorAggregate
{
	/** @var SplQueue */
	private $routes;

	/** @var array */
	private $cachedRoutes;



	public function __construct()
	{
		$this->routes = new /*SplQueue*//**/ArrayList/**/;
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(/*Nette\Web\*/IHttpRequest $httpRequest)
	{
		foreach ($this->routes as $route) {
			$appRequest = $route->match($httpRequest);
			if ($appRequest !== NULL) {
				return $appRequest;
			}
		}
		return NULL;
	}



	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @param  PresenterRequest
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $appRequest, /*Nette\Web\*/IHttpRequest $httpRequest)
	{
		if ($this->cachedRoutes === NULL) {
			$routes = array();
			$routes['*'] = array();

			foreach ($this->routes as $route) {
				$presenter = $route instanceof Route ? $route->getTargetPresenter() : NULL;

				if ($presenter === FALSE) continue;

				if (is_string($presenter)) {
					$presenter = strtolower($presenter);
					if (!isset($routes[$presenter])) {
						$routes[$presenter] = $routes['*'];
					}
					$routes[$presenter][] = $route;

				} else {
					foreach ($routes as $id => $foo) {
						$routes[$id][] = $route;
					}
				}
			}

			$this->cachedRoutes = $routes;
		}

		$presenter = strtolower($appRequest->getPresenterName());
		if (!isset($this->cachedRoutes[$presenter])) $presenter = '*';

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$uri = $route->constructUrl($appRequest, $httpRequest);
			if ($uri !== NULL) {
				return $uri;
			}
		}

		return NULL;
	}



	/********************* interfaces ArrayAccess, Countable & IteratorAggregate ****************d*g**/



	/**
	 * Adds the router.
	 * @param  mixed
	 * @param  IRouter
	 * @return void
	 */
	public function offsetSet($index, $route)
	{
		if (!($routes instanceof IRouter)) {
			throw new /*\*/InvalidArgumentException("Argument must be IRouter descendant.");
		}
		$this->routes[$index] = $route;
	}



	/**
	 * Returns router specified by index. Throws exception if router doesn't exist.
	 * @param  mixed
	 * @return IRouter
	 */
	public function offsetGet($index)
	{
		return $this->routes[$index];
	}



	/**
	 * Does router specified by index exists?
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->routes[$index]);
	}



	/**
	 * Removes router.
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($index)
	{
		unset($this->routes[$index]);
	}



	/**
	 * Iterates over routers.
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return $this->routes;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->routes);
	}

}
