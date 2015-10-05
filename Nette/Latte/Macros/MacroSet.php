<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Latte\Macros;

use Nette;
use Nette\Latte;
use Nette\Latte\MacroNode;


/**
 * Base IMacro implementation. Allows add multiple macros.
 *
 * @author     David Grudl
 */
class MacroSet extends Nette\Object implements Latte\IMacro
{
	/** @var Latte\Compiler */
	private $compiler;

	/** @var array */
	private $macros;


	public function __construct(Latte\Compiler $compiler)
	{
		$this->compiler = $compiler;
	}


	public function addMacro($name, $begin, $end = NULL, $attr = NULL)
	{
		foreach (array($begin, $end, $attr) as $arg) {
			if ($arg && !is_string($arg)) {
				Nette\Utils\Callback::check($arg);
			}
		}

		$this->macros[$name] = array($begin, $end, $attr);
		$this->compiler->addMacro($name, $this);
		return $this;
	}


	public static function install(Latte\Compiler $compiler)
	{
		return new static($compiler);
	}


	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
	}


	/**
	 * New node is found.
	 * @return bool
	 */
	public function nodeOpened(MacroNode $node)
	{
		list($begin, $end, $attr) = $this->macros[$node->name];
		$node->isEmpty = !$end;

		if ($attr && $node->prefix === $node::PREFIX_NONE) {
			$node->isEmpty = TRUE;
			$this->compiler->setContext(Latte\Compiler::CONTEXT_DOUBLE_QUOTED_ATTR);
			$res = $this->compile($node, $attr);
			if ($res === FALSE) {
				return FALSE;
			} elseif (!$node->attrCode) {
				$node->attrCode = "<?php $res ?>";
			}
			$this->compiler->setContext(NULL);

		} elseif ($begin) {
			$res = $this->compile($node, $begin);
			if ($res === FALSE) {
				return FALSE;
			} elseif (!$node->openingCode) {
				$node->openingCode = "<?php $res ?>";
			}

		} elseif (!$end) {
			return FALSE;
		}
	}


	/**
	 * Node is closed.
	 * @return void
	 */
	public function nodeClosed(MacroNode $node)
	{
		if (isset($this->macros[$node->name][1])) {
			$res = $this->compile($node, $this->macros[$node->name][1]);
			if (!$node->closingCode) {
				$node->closingCode = "<?php $res ?>";
			}
		}
	}


	/**
	 * Generates code.
	 * @return string
	 */
	private function compile(MacroNode $node, $def)
	{
		$node->tokenizer->reset();
		$writer = Latte\PhpWriter::using($node, $this->compiler);
		if (is_string($def)) {
			return $writer->write($def);
		} else {
			return Nette\Utils\Callback::invoke($def, $node, $writer);
		}
	}


	/**
	 * @return Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
