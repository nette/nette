<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationTracy;

use Nette,
	Nette\Application\Routers,
	Nette\Application\UI\Presenter,
	Tracy;


/**
 * Routing debugger for Debug Bar.
 *
 * @author     David Grudl
 */
class RoutingPanel extends Nette\Object implements Tracy\IBarPanel
{
	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var array */
	private $routers = array();

	/** @var Nette\Application\Request */
	private $request;

	/** @var ReflectionClass|ReflectionMethod */
	private $source;


	public static function initializePanel(Nette\Application\Application $application)
	{
		Tracy\Debugger::getBlueScreen()->addPanel(function($e) use ($application) {
			return $e ? NULL : array(
				'tab' => 'Nette Application',
				'panel' => '<h3>Requests</h3>' . Tracy\Dumper::toHtml($application->getRequests())
					. '<h3>Presenter</h3>' . Tracy\Dumper::toHtml($application->getPresenter())
			);
		});
	}


	public function __construct(Nette\Application\IRouter $router, Nette\Http\IRequest $httpRequest, Nette\Application\IPresenterFactory $presenterFactory)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * Renders tab.
	 * @return string
	 */
	public function getTab()
	{
		$this->analyse($this->router);
		ob_start();
		require __DIR__ . '/templates/RoutingPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		return ob_get_clean();
	}


	/**
	 * Analyses simple route.
	 * @param  Nette\Application\IRouter
	 * @return void
	 */
	private function analyse($router, $module = '')
	{
		if ($router instanceof Routers\RouteList) {
			foreach ($router as $subRouter) {
				$this->analyse($subRouter, $module . $router->getModule());
			}
			return;
		}

		$matched = 'no';
		$request = $router->match($this->httpRequest);
		if ($request) {
			$request->setPresenterName($module . $request->getPresenterName());
			$matched = 'may';
			if (empty($this->request)) {
				$this->request = $request;
				$this->findSource();
				$matched = 'yes';
			}
		}

		$this->routers[] = array(
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routers\Route || $router instanceof Routers\SimpleRouter ? $router->getDefaults() : array(),
			'mask' => $router instanceof Routers\Route ? $router->getMask() : NULL,
			'request' => $request,
			'module' => rtrim($module, ':')
		);
	}


	private function findSource()
	{
		$request = $this->request;
		$presenter = $request->getPresenterName();
		try {
			$class = $this->presenterFactory->getPresenterClass($presenter);
		} catch (Nette\Application\InvalidPresenterException $e) {
			return;
		}
		$rc = Nette\Reflection\ClassType::from($class);

		if ($rc->isSubclassOf('Nette\Application\UI\Presenter')) {
			if (isset($request->parameters[Presenter::SIGNAL_KEY])) {
				$method = $class::formatSignalMethod($request->parameters[Presenter::SIGNAL_KEY]);

			} elseif (isset($request->parameters[Presenter::ACTION_KEY])) {
				$action = $request->parameters[Presenter::ACTION_KEY];
				$method = $class::formatActionMethod($action);
				if (!$rc->hasMethod($method)) {
					$method = $class::formatRenderMethod($action);
				}
			}
		}

		$this->source = isset($method) && $rc->hasMethod($method) ? $rc->getMethod($method) : $rc;
	}

}
