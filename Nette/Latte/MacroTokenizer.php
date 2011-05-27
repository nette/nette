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
 * Macro tokenizer.
 *
 * @author     David Grudl
 */
class MacroTokenizer extends Nette\Utils\Tokenizer
{
	/** @internal */
	const T_WHITESPACE = T_WHITESPACE,
		T_COMMENT = T_COMMENT,
		T_SYMBOL = -1,
		T_NUMBER = -2,
		T_VARIABLE = -3;



	public function __construct($input)
	{
		parent::__construct(array(
			self::T_WHITESPACE => '\s+',
			self::T_COMMENT => '(?s)/\*.*?\*/',
			Parser::RE_STRING,
			'(?:true|false|null|and|or|xor|clone|new|instanceof|return|continue|break|[A-Z_][A-Z0-9_]{2,})(?![\d\pL_])', // keyword or const
			'\([a-z]+\)', // type casting
			self::T_VARIABLE => '\$[\d\pL_]+',
			self::T_NUMBER => '[+-]?[0-9]+(?:\.[0-9]+)?(?:e[0-9]+)?',
			self::T_SYMBOL => '[\d\pL_]+(?:-[\d\pL_]+)*',
			'::|=>|[^"\']', // =>, any char except quotes
		), 'u');
		$this->tokenize($input);
	}

}
