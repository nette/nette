<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * The exception occured during Latte compilation.
 *
 * @author     David Grudl
 */
class CompileException extends Nette\InvalidStateException
{
	/** @var string */
	public $sourceCode;

	/** @var string */
	public $sourceName;

	/** @var int */
	public $sourceLine;


	public function setSource($code, $line, $name = NULL)
	{
		$this->sourceCode = (string) $code;
		$this->sourceLine = (int) $line;
		$this->sourceName = (string) $name;
		if (is_file($name)) {
			$this->message = rtrim($this->message, '.')
				. ' in ' . str_replace(dirname(dirname($name)), '...', $name) . ($line ? ":$line" : '');
		}
		return $this;
	}

}



/**
 * The exception that indicates tokenizer error.
 */
class TokenizerException extends \Exception
{
}

class_alias('Nette\Latte\CompileException', 'Nette\Latte\ParseException');
class_alias('Nette\Latte\CompileException', 'Nette\Templating\FilterException');
