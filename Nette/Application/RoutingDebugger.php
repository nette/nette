<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Routing debugger for Debug Bar.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class RoutingDebugger extends /*Nette\*/DebugPanel
{
	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Web\IHttpRequest */
	private $httpRequest;

	/** @var ArrayObject */
	private $routers;

	/** @var Nette\Application\PresenterRequest */
	private $request;



	public function __construct(IRouter $router, /*Nette\Web\*/IHttpRequest $httpRequest)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->routers = new /*\*/ArrayObject;
		parent::__construct('RoutingDebugger', array($this, 'renderTab'), array($this, 'renderPanel'));
	}



	/**
	 * Renders debuger tab.
	 * @return void
	 */
	public function renderTab()
	{
		$this->analyse($this->router);
		require dirname(__FILE__) . '/templates/RoutingDebugger.tab.phtml';
	}



	/**
	 * Renders debuger panel.
	 * @return void
	 */
	public function renderPanel()
	{
		require dirname(__FILE__) . '/templates/RoutingDebugger.panel.phtml';
	}



	/**
	 * Analyses simple route.
	 * @param  Nette\Application\IRouter
	 * @return void
	 */
	private function analyse($router)
	{
		if ($router instanceof MultiRouter) {
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
			'defaults' => $router instanceof Route || $router instanceof SimpleRouter ? $router->getDefaults() : array(),
			'mask' => $router instanceof Route ? $router->getMask() : NULL,
			'request' => $request,
		);
	}

}
