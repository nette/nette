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
use Nette\Utils\Strings;



/**
 * Base addon class.
 * 
 * @author     Jan Jakes
 */
abstract class Addon extends Nette\Object
{

	/** @var string */
	protected $name;

	/** @var \SystemContainer|Nette\DI\Container */
	protected $container;



	/**
	 * @param  Container
	 */
	public function setContainer(Nette\DI\Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @return string
	 */
	final public function getName()
	{
		if ($this->name !== NULL) {
			return $this->name;
		}

		$name = get_class($this);
		if (FALSE !== $pos = strrpos($name, '\\')) {
			return $this->name = Strings::replace(lcfirst(substr($name, $pos + 1)), '/Addon$/');

		} elseif (FLASE !== $pos = strrpos($name, '_')) {
			return $this->name = Strings::replace(lcfirst(substr($name, $pos + 1)), '/Addon$/');

		} else {
			return $this->name = $name;
		}
	}



	/**
	 * @param  string
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
	}




	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->getReflection()->getNamespaceName();
	}



	/**
	 * @return string
	 */
	public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}



	/**
	 * Builds addon. It is only ever called once when the cache is empty.
	 * @param  Compiler
	 */
	public function compile(Nette\Config\Configurator $configurator, Nette\Config\Compiler $compiler, AddonContainer $addonContainer)
	{
	}



	/**
	 * Occurs before the application loads presenter.
	 */
	public function startup()
	{
	}



	/**
	 * Occurs when a new request is ready for dispatch.
	 * @param  Request
	 */
	public function request(Nette\Application\Request $request)
	{
	}



	/**
	 * Occurs when a new response is received.
	 * @param  IResponse
	 */
	public function response(Nette\Application\IResponse $response)
	{
	}



	/**
	 * Occurs when an unhandled exception occurs in the application.
	 * @param  \Exception
	 */
	public function error(\Exception $e)
	{
	}



	/**
	 * Occurs before the application shuts down.
	 * @param  \Exception|NULL
	 */
	public function shutdown(\Exception $e = NULL)
	{
	}

}
