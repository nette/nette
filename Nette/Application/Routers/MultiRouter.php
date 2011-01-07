<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Application;

use Nette;



/**
 * The router broker.
 *
 * @author     David Grudl
 */
class MultiRouter extends Nette\ArrayList implements IRouter
{
	/** @var array */
	private $cachedRoutes;

	/** @var string */
	private $module;



	public function __construct($module = NULL)
	{
		$this->module = $module ? $module . ':' : '';
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(Nette\Web\IHttpRequest $httpRequest)
	{
		foreach ($this as $route) {
			$appRequest = $route->match($httpRequest);
			if ($appRequest !== NULL) {
				$appRequest->setPresenterName($this->module . $appRequest->getPresenterName());
				return $appRequest;
			}
		}
		return NULL;
	}



	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * @param  PresenterRequest
	 * @param  Nette\Web\Uri
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $appRequest, Nette\Web\Uri $refUri)
	{
		if ($this->cachedRoutes === NULL) {
			$routes = array();
			$routes['*'] = array();

			foreach ($this as $route) {
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

		if ($this->module) {
			if (strncasecmp($tmp = $appRequest->getPresenterName(), $this->module, strlen($this->module)) === 0) {
				$appRequest = clone $appRequest;
				$appRequest->setPresenterName(substr($tmp, strlen($this->module)));
			} else {
				return NULL;
			}
		}

		$presenter = strtolower($appRequest->getPresenterName());
		if (!isset($this->cachedRoutes[$presenter])) $presenter = '*';

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$uri = $route->constructUrl($appRequest, $refUri);
			if ($uri !== NULL) {
				return $uri;
			}
		}

		return NULL;
	}



	/**
	 * Adds the router.
	 * @param  mixed
	 * @param  IRouter
	 * @return void
	 */
	public function offsetSet($index, $route)
	{
		if (!$route instanceof IRouter) {
			throw new \InvalidArgumentException("Argument must be IRouter descendant.");
		}
		parent::offsetSet($index, $route);
	}

}
