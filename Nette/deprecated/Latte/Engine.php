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
		$this->onCompile[] = function($latte) {
			$latte->getParser()->shortNoEscape = TRUE;
			$latte->getCompiler()->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($latte->getCompiler()));
			Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
			Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());
		};

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

}
