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
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\DI\Container;
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

	/** @var Container */
	protected $container;



	/**
	 * @param  Container
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @return string
	 */
	public function getName()
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
	public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}



	/**
	 * @param  Compiler
	 */
	public function compile(Configurator $configurator, Compiler $compiler)
	{
	}



	/**
	 * 
	 */
	public function startup()
	{
	}



	/**
	 * @param  Request
	 */
	public function request(Request $request)
	{
	}



	/**
	 * @param  IResponse
	 */
	public function response(IResponse $response)
	{
	}



	/**
	 * @param  \Exception
	 */
	public function error(\Exception $e)
	{
	}



	/**
	 * @param  \Exception|NULL
	 */
	public function shutdown(\Exception $e = NULL)
	{
	}

}
