<?php

/*use Nette\Object; */
/*use Nette\Debug; */
/*use Nette\Environment; */
/*use Nette\Application\IRouter; */
/*use Nette\Application\MultiRouter; */
/*use Nette\Application\SimpleRouter; */
/*use Nette\Application\Route; */
/*use Nette\Templates\Template; */
/*use Nette\Web\IHttpRequest; */


/**
 * Routing debugger for Nette Framework.
 *
 * @copyright  Copyright (c) 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 */
class RoutingDebugger extends Object
{
	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Web\IHttpRequest */
	private $httpRequest;

	/** @var Nette\Templates\Template */
	private $template;



	/**
	 * Enables routing debugger.
	 * @return void
	 */
	public static function enable()
	{
		if (!Environment::isProduction() && !Environment::getHttpRequest()->isAjax()) {
			$debugger = new self(Environment::getApplication()->getRouter(), Environment::getHttpRequest());
			register_shutdown_function(array($debugger, 'paint'));
		}
	}



	public function __construct(IRouter $router, IHttpRequest $httpRequest)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
	}



	/**
	 * Renders debuger output.
	 * @return void
	 */
	public function paint()
	{
		if (strncmp(Environment::getHttpResponse()->getHeader('Content-Type', 'text/html'), 'text/html', 9)) {
			return;
		}

		$this->template = new Template;
		$this->template->setFile(dirname(__FILE__) . '/RoutingDebugger.phtml');
		$this->template->routers = array();
		$this->analyse($this->router);
		$this->template->render();
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

		$appRequest = $router->match($this->httpRequest);
		$matched = $appRequest === NULL ? 'no' : 'may';
		if ($appRequest !== NULL && !isset($this->template->request)) {
			$this->template->request = $appRequest;
			$matched = 'yes';
		}

		$this->template->routers[] = array(
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Route || $router instanceof SimpleRouter ? $router->getDefaults() : array(),
			'mask' => $router instanceof Route ? $router->getMask() : NULL,
			'request' => $appRequest,
		);
	}

}
