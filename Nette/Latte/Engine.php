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
		$this->parser->handler = new DefaultMacros;
		$this->parser->macros = DefaultMacros::$defaultMacros;
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		$this->parser->context = Parser::CONTEXT_TEXT;
		$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
		$this->parser->setDelimiters('\\{(?![\\s\'"{}*])', '\\}');
		return $this->parser->parse($s);
	}

}
