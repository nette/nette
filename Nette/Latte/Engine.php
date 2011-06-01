<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette;



/**
 * Templating engine Latte.
 *
 * @author     David Grudl
 */
class Engine extends Nette\Object
{
	/** @var Parser */
	public $parser;



	public function __construct()
	{
		$this->parser = new Parser;
		Macros\CoreMacros::install($this->parser);
		$this->parser->addMacro('cache', new Macros\CacheMacro($this->parser));
		Macros\UIMacros::install($this->parser);
		Macros\FormMacros::install($this->parser);
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		$this->parser->context = array(Parser::CONTEXT_TEXT);
		$this->parser->setDelimiters('\\{(?![\\s\'"{}])', '\\}');
		return $this->parser->parse($s);
	}

}
