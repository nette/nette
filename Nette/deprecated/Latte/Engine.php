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
	private $fixed = FALSE;

	public function __construct()
	{
		$this->getParser()->shortNoEscape = TRUE;
		$this->addFilter('url', 'rawurlencode');
		foreach (array('normalize', 'toAscii', 'webalize', 'padLeft', 'padRight', 'reverse') as $name) {
			$this->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}
	}


	public function __invoke($s)
	{
		trigger_error(__METHOD__ . '() is deprecated; use compile() instead.', E_USER_DEPRECATED);
		return $this->setLoader(new Latte\Loaders\StringLoader)->compile($s);
	}


	public function getCompiler()
	{
		$compiler = parent::getCompiler();
		if (!$this->fixed) {
			$this->fixed = TRUE;
			$compiler->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($compiler));
			Nette\Bridges\ApplicationLatte\UIMacros::install($compiler);
			Nette\Bridges\FormsLatte\FormMacros::install($compiler);
		}
		return $compiler;
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
