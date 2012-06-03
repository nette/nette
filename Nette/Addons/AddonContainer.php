<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Addons;

use Nette;



/**
 * Addon container.
 * 
 * @author     Jan Jakes
 */
class AddonContainer extends Nette\Object
{

	/** @var Addon[] */
	private $addons = array();

	/** @var Addon[] */
	private $classMap = array();



	/**
	 * @param  Addon[]
	 */
	public function __construct($addons)
	{
		foreach ($addons as $name => $addon) {
			if (!($addon instanceof Addon)) {
				throw new Nette\InvalidArgumentException("Class '" . get_class($addon) . "' is not a subclass of Nette\Addons\Addon.");
			}

			if (is_string($name)) {
				$addon->setName($name);
			} else {
				$name = $addon->getName();
			}

			if (isset($this->addons[$name])) {
				throw new Nette\InvalidStateException("Addon '$name' already defined.");
			}

			$class = get_class($addon);
			if (isset($this->classMap[$class])) {
				throw new Nette\InvalidStateException("Addon of type '$class' already defined.");
			}

			$this->addons[$name] = $this->classMap[$class] = $addon;			
		}
	}



	/**
	 * @param  string
	 * @return Addon
	 */
	public function getAddon($name)
	{
		if (!isset($this->addons[$name])) {
			throw new Nette\InvalidStateException("Addon '$name' not found.");
		}

		return $this->addons[$name];
	}



	/**
	 * @return Addon[]
	 */
	public function getAddons()
	{
		return $this->addons;
	}



	/**
	 * @param  string
	 * @return Addon
	 */
	public function getAddonByType($class)
	{
		if (!isset($this->classMap[$class])) {
			throw new Nette\InvalidStateException("Addon of type '$class' not found.");
		}

		return $this->classMap[$class];
	}



	/**
	 * @param  Nette\DI\Container
	 */
	public function setContainer(Nette\DI\Container $container)
	{
		foreach ($this->addons as $addon) {
			$addon->setContainer($container);
		}
	}



	/**
	 * Builds addons. It is only ever called once when the cache is empty.
	 * @param  Nette\Config\Configurator
	 * @param  Nette\Config\Compiler
	 */
	public function compile(Nette\Config\Configurator $configurator, Nette\Config\Compiler $compiler)
	{
		foreach ($this->addons as $addon) {
			$addon->compile($configurator, $compiler, $this);
		}
	}



	/**
	 * Attaches Nette\Application\Application events to addons.
	 * @param  Nette\Application\Application
	 */
	public function attachApplicationEvents(Nette\Application\Application $application)
	{
		$application->onStartup[] = array($this, 'startup');
		$application->onRequest[] = array($this, 'request');
		$application->onResponse[] = array($this, 'response');
		$application->onError[] = array($this, 'error');
		$application->onShutdown[] = array($this, 'shutdown');
	}



	/**
	 * Occurs before the application loads presenter.
	 * @param  Nette\Application\Application
	 */
	public function startup(Nette\Application\Application $application)
	{
		foreach ($this->addons as $addon) {
			$addon->startup();
		}
	}



	/**
	 * Occurs when a new request is ready for dispatch.
	 * @param  Nette\Application\Application
	 * @param  Nette\Application\Request
	 */
	public function request(Nette\Application\Application $application, Nette\Application\Request $request)
	{
		foreach ($this->addons as $addon) {
			$addon->request($request);
		}
	}



	/**
	 * Occurs when a new response is received.
	 * @param  Nette\Application\Application
	 * @param  Nette\Application\IResponse
	 */
	public function response(Nette\Application\Application $application, Nette\Application\IResponse $response)
	{
		foreach ($this->addons as $addon) {
			$addon->response($response);
		}
	}



	/**
	 * Occurs when an unhandled exception occurs in the application.
	 * @param  Nette\Application\Application
	 * @param  \Exception
	 */
	public function error(Nette\Application\Application $application, \Exception $e)
	{
		foreach ($this->addons as $addon) {
			$addon->error($e);
		}
	}



	/**
	 * Occurs before the application shuts down.
	 * @param  Nette\Application\Application
	 * @param  \Exception|NULL
	 */
	public function shutdown(Nette\Application\Application $application, \Exception $e = NULL)
	{
		foreach ($this->addons as $addon) {
			$addon->shutdown($e);
		}
	}

}
