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

	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		$parser = new Parser;
		$parser->setDelimiters('\\{(?![\\s\'"{}*])', '\\}');

		// context-aware escaping
		$parser->escape = '$template->escape';

		// initialize handlers
		$parser->handler = new DefaultMacros;
		$parser->handler->initialize($parser, $s);

		// process all {tags} and <tags/>
		$s = $parser->parse($s);

		$parser->handler->finalize($s);

		return $s;
	}

}
