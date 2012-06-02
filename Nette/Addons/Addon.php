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
use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\Utils\Finder;
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
	final public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}



	/**
	 * @param  Configurator
	 * @param  Compiler
	 */
	public function compile(Configurator $configurator, Compiler $compiler)
	{
	}

}
