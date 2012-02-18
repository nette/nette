<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette;



/**
 * Latte parser token.
 *
 * @author     David Grudl
 */
class Token extends Nette\Object
{
	const TEXT = 'text',
		MACRO_TAG = 'macroTag',
		HTML_TAG_BEGIN = 'htmlTagBegin',
		HTML_TAG_END = 'htmlTagEnd',
		HTML_ATTRIBUTE = 'htmlAttribute',
		COMMENT = 'comment';

	/** @var int */
	public $type;

	/** @var string */
	public $text;

	/** @var int */
	public $line;

	/** @var string  MACRO_TAG, HTML_TAG_BEGIN, HTML_ATTRIBUTE */
	public $name;

	/** @var string  MACRO_TAG, HTML_ATTRIBUTE */
	public $value;

	/** @var string  MACRO_TAG */
	public $modifiers;

	/** @var bool  HTML_TAG_BEGIN */
	public $closing;

}
