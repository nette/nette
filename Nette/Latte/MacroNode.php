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
 */
class MacroNode extends Nette\Object
{
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

	/** @var MacroTokenizer */
	public $tokenizer;

	/** @var int @internal */
	public $offset;

	/** @var MacroNode */
	public $parentNode;

	/** @var string */
	public $content;

	/** @var stdClass  user data */
	public $data;

	/** @var HtmlNode  for n:attr macros */
	public $htmlNode;



	public function __construct(IMacro $macro, $name, $args = NULL, $modifiers = NULL, MacroNode $parentNode = NULL, HtmlNode $htmlNode = NULL)
	{
		$this->macro = $macro;
		$this->name = (string) $name;
		$this->modifiers = (string) $modifiers;
		$this->parentNode = $parentNode;
		$this->htmlNode = $htmlNode;
		$this->tokenizer = new MacroTokenizer($this->args);
		$this->data = new \stdClass;
		$this->setArgs($args);
	}



	public function setArgs($args)
	{
		$this->args = (string) $args;
		$this->tokenizer->tokenize($this->args);
	}



	public function close($content)
	{
		$this->closing = TRUE;
		$this->content = $content;
		return $this->macro->nodeClosed($this);
	}

}
