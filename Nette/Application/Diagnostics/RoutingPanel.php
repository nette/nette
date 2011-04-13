<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application\Diagnostics;

use Nette,
	Nette\Application\Routers,
	Nette\Application\UI\Presenter, // templates
	Nette\Diagnostics\Debugger;



/**
 * Routing debugger for Debug Bar.
 *
 * @author     David Grudl
 */
class RoutingPanel extends Nette\Diagnostics\Panel
{
	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var array */
	private $routers = array();

	/** @var Nette\Application\Request */
	private $request;



	public function __construct(Nette\Application\IRouter $router, Nette\Http\IRequest $httpRequest)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		parent::__construct('RoutingPanel', array($this, 'renderTab'), array($this, 'renderPanel'));
	}



	/**
	 * Renders debuger tab.
	 * @return void
	 */
	public function renderTab()
	{
		$this->analyse($this->router);
		require __DIR__ . '/templates/RoutingPanel.tab.phtml';
	}



	/**
	 * Renders debuger panel.
	 * @return void
	 */
	public function renderPanel()
	{
		require __DIR__ . '/templates/RoutingPanel.panel.phtml';
	}



	/**
	 * Analyses simple route.
	 * @param  Nette\Application\IRouter
	 * @return void
	 */
	private function analyse($router)
	{
		if ($router instanceof Routers\RouteList) {
			foreach ($router as $subRouter) {
				$this->analyse($subRouter);
			}
			return;
		}

		$request = $router->match($this->httpRequest);
		$matched = $request === NULL ? 'no' : 'may';
		if ($request !== NULL && empty($this->request)) {
			$this->request = $request;
			$matched = 'yes';
		}

		$this->routers[] = array(
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routers\Route || $router instanceof Routers\SimpleRouter ? $router->getDefaults() : array(),
			'mask' => $router instanceof Routers\Route ? $router->getMask() : NULL,
			'request' => $request,
		);
	}

}
