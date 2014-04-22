<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette,
	Latte;


/**
 * @deprecated
 */
class Engine extends Latte\Engine
{

	public function __construct()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
	}


	public function & __get($name)
	{
		switch (strtolower($name)) {
			case 'parser':
			case 'compiler':
				$method = 'get' . ucfirst($name);
				trigger_error("Magic getters are deprecated. Use $method() method instead.", E_USER_DEPRECATED);
				$return = $this->$method(); // return by reference
				return $return;
		}

		return parent::__get($name);
	}

}
