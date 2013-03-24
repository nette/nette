<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette,
	Nette\Utils\Neon;



/**
 * Assignment or calling statement.
 *
 * @author     David Grudl
 */
class Statement extends Nette\Object
{
	/** @var string  class|method|$property */
	public $entity;

	/** @var array */
	public $arguments;



	public function __construct($entity, array $arguments = array())
	{
		$this->entity = $entity;
		$this->arguments = $arguments;
	}



	/**
	 * @internal
	 * @return string
	 */
	public function serialize()
	{
		$entity = new Nette\Utils\NeonEntity();
		$entity->value = $this->entity;
		$entity->attributes = $this->arguments;
		return Neon::encode($entity);
	}



	/**
	 * @internal
	 * @return string
	 */
	public function __toString()
	{
		return $this->serialize();
	}

}
