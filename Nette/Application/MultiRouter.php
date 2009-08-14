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
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



require_once dirname(__FILE__) . '/../Application/IRouter.php';

require_once dirname(__FILE__) . '/../Collections/ArrayList.php';



/**
 * The router broker.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
class MultiRouter extends /*Nette\Collections\*/ArrayList implements IRouter
{
	/** @var array {@link MultiRouter::constructUrl()} */
	private $cachedRoutes;



	public function __construct()
	{
		parent::__construct(NULL, /*Nette\Application\*/'IRouter');
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(/*Nette\Web\*/IHttpRequest $httpRequest)
	{
		foreach ($this as $route) {
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

			foreach ($this as $route) {
				$presenter = $route instanceof Route ? $route->getTargetPresenter() : NULL;

				if ($presenter === FALSE) continue;

				if (is_string($presenter)) {
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

		$presenter = $appRequest->getPresenterName();
		if (!isset($this->cachedRoutes[$presenter])) $presenter = '*';

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$uri = $route->constructUrl($appRequest, $httpRequest);
			if ($uri !== NULL) {
				return $uri;
			}
		}

		return NULL;
	}

}
