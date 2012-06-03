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
use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\IResponse;
use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\DI\Container;



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
	 * @param  Container
	 */
	public function setContainer(Container $container)
	{
		foreach ($this->addons as $addon) {
			$addon->setContainer($container);
		}
	}



	/**
	 * @param  Configurator
	 * @param  Compiler
	 */
	public function compile(Configurator $config, Compiler $compiler)
	{
		foreach ($this->addons as $addon) {
			$addon->compile($config, $compiler);
		}
	}



	/**
	 * @param  Application
	 */
	public function attachApplicationEvents(Application $application)
	{
		$application->onStartup[] = array($this, 'startup');
		$application->onRequest[] = array($this, 'request');
		$application->onResponse[] = array($this, 'response');
		$application->onError[] = array($this, 'error');
		$application->onShutdown[] = array($this, 'shutdown');
	}



	/**
	 * @param  Application
	 */
	public function startup(Application $application)
	{
		foreach ($this->addons as $addon) {
			$addon->startup();
		}
	}



	/**
	 * @param  Application
	 * @param  Request
	 */
	public function request(Application $application, Request $request)
	{
		foreach ($this->addons as $addon) {
			$addon->request($request);
		}
	}



	/**
	 * @param  Application
	 * @param  IResponse
	 */
	public function response(Application $application, IResponse $response)
	{
		foreach ($this->addons as $addon) {
			$addon->response($response);
		}
	}



	/**
	 * @param  Application
	 * @param  \Exception
	 */
	public function error(Application $application, \Exception $e)
	{
		foreach ($this->addons as $addon) {
			$addon->error($e);
		}
	}



	/**
	 * @param  Application
	 * @param  \Exception|NULL
	 */
	public function shutdown(Application $application, \Exception $e = NULL)
	{
		foreach ($this->addons as $addon) {
			$addon->shutdown($e);
		}
	}

}
