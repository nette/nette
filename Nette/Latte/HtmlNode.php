<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * HTML element node.
 *
 * @author     David Grudl
 */
class HtmlNode extends Nette\Object
{
	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var array */
	public $attrs = array();

	/** @var array */
	public $macroAttrs = array();

	/** @var bool */
	public $closing = FALSE;

	/** @var HtmlNode */
	public $parentNode;

	/** @var string */
	public $attrCode;

	/** @var int */
	public $offset;


	public function __construct($name, self $parentNode = NULL)
	{
		$this->name = $name;
		$this->parentNode = $parentNode;
	}

}
