<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Latte;

use Nette;



/**
 * Macro element node.
 *
 * @author     David Grudl
 * @internal
 */
class MacroNode extends Nette\Object
{
	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var array */
	public $attrs = array();

	/** @var string */
	public $content;

	/** @var string */
	public $modifiers;

	/** @var bool */
	public $closing = FALSE;

	/** @var int */
	public $offset;



	public function __construct($name, $content = NULL, $modifiers = NULL)
	{
		$this->name = $name;
		$this->content = $content;
		$this->modifiers = $modifiers;
	}

}
