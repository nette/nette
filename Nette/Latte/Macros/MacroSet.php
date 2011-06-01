<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte\Macros;

use Nette,
	Nette\Latte,
	Nette\Latte\MacroNode;



/**
 * Base IMacro implementation. Allowes add multiple macros.
 *
 * @author     David Grudl
 */
class MacroSet extends Nette\Object implements Latte\IMacro
{
	/** @var Latte\Parser */
	public $parser;

	/** @var array */
	private $macros;



	public function __construct(Latte\Parser $parser)
	{
		$this->parser = $parser;
	}



	public function addMacro($name, $begin, $end = NULL)
	{
		$this->macros[$name] = array($begin, $end);
		$this->parser->addMacro($name, $this);
	}



	public static function install(Latte\Parser $parser)
	{
		return new static($parser);
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
	 * @return bool|string
	 */
	public function nodeOpened(MacroNode $node)
	{
		$node->isEmpty = !isset($this->macros[$node->name][1]);
		return $this->compile($node, $this->macros[$node->name][0]);
	}



	/**
	 * Node is closed.
	 * @return string
	 */
	public function nodeClosed(MacroNode $node)
	{
		return $this->compile($node, $this->macros[$node->name][1]);
	}



	/**
	 * Generates code.
	 * @return string
	 */
	private function compile(MacroNode $node, $def)
	{
		$writer = Latte\PhpWriter::using($node, $this->parser->context);
		if (is_string($def)/*5.2* && substr($def, 0, 1) !== "\0"*/) {
			$code = $writer->write($def);
		} else {
			$code = callback($def)->invoke($node, $writer);
			if ($code === FALSE) {
				return FALSE;
			}
		}
		return "<?php $code ?>";
	}

}
