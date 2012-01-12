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
		MACRO = 'macro',
		TAG_BEGIN = 'tag_begin',
		TAG_END = 'tag_end',
		ATTRIBUTE = 'attribute',
		COMMENT = 'comment';

	/** @var int */
	public $type;

	/** @var string */
	public $text;

	/** @var int */
	public $line;

	/** @var string  MACRO, TAG_BEGIN, ATTRIBUTE */
	public $name;

	/** @var string  MACRO, ATTRIBUTE */
	public $value;

	/** @var string  MACRO */
	public $modifiers;

	/** @var bool  TAG_BEGIN */
	public $closing;

}
