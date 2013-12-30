<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * Macro element node.
 *
 * @author     David Grudl
 */
class MacroNode extends Nette\Object
{
	const PREFIX_INNER = 'inner',
		PREFIX_TAG = 'tag',
		PREFIX_NONE = 'none';

	/** @var IMacro */
	public $macro;

	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var string  raw arguments */
	public $args;

	/** @var string  raw modifier */
	public $modifiers;

	/** @var bool */
	public $closing = FALSE;

	/** @var MacroTokens */
	public $tokenizer;

	/** @var MacroNode */
	public $parentNode;

	/** @var string */
	public $openingCode;

	/** @var string */
	public $closingCode;

	/** @var string */
	public $attrCode;

	/** @var string */
	public $content;

	/** @var \stdClass  user data */
	public $data;

	/** @var HtmlNode  closest HTML node */
	public $htmlNode;

	/** @var string  indicates n:attribute macro and type of prefix (PREFIX_INNER, PREFIX_TAG, PREFIX_NONE) */
	public $prefix;

	public $saved;


	public function __construct(IMacro $macro, $name, $args = NULL, $modifiers = NULL, self $parentNode = NULL, HtmlNode $htmlNode = NULL, $prefix = NULL)
	{
		$this->macro = $macro;
		$this->name = (string) $name;
		$this->modifiers = (string) $modifiers;
		$this->parentNode = $parentNode;
		$this->htmlNode = $htmlNode;
		$this->prefix = $prefix;
		$this->data = new \stdClass;
		$this->setArgs($args);
	}


	public function setArgs($args)
	{
		$this->args = (string) $args;
		$this->tokenizer = new MacroTokens($this->args);
	}

}
