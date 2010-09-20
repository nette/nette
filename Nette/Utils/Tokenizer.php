<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette;

use Nette;



/**
 * Simple tokenizer.
 *
 * @author     David Grudl
 */
class Tokenizer extends Object implements \IteratorAggregate
{
	/** regular expression for single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	const T_WHITESPACE = T_WHITESPACE;

	const T_COMMENT = T_COMMENT;

	/** @var string */
	private $input;

	/** @var array */
	public $tokens;

	/** @var string */
	private $re;

	/** @var array */
	private $names;



	/**
	 * Lexical scanner.
	 * @param  string
	 * @return void
	 */
	function __construct(array $patterns, $flags = '')
	{
		$this->re = '~(' . implode(')|(', $patterns) . ')~A' . $flags;
		$keys = array_keys($patterns);
		$this->names = $keys === range(0, count($patterns) - 1) ? FALSE : $keys;
	}



	function tokenize($input)
	{
		$this->input = $input;
		if ($this->names) {
			$this->tokens = String::matchAll($input, $this->re);
			$len = 0;
			foreach ($this->tokens as & $match) {
				$name = NULL;
				for ($i = 1; $i < count($this->names); $i++) {
					if (!isset($match[$i])) {
						break;
					} elseif ($match[$i] != NULL) {
						$name = $this->names[$i - 1]; break;
					}
				}
				$match = array($match[0], $name);
				$len += strlen($match[0]);
			}
			if ($len !== strlen($input)) {
				$errorOffset = $len;
			}

		} else {
			$this->tokens = String::split($input, $this->re, PREG_SPLIT_NO_EMPTY);
			if ($this->tokens && !String::match(end($this->tokens), $this->re)) {
				$tmp = String::split($this->input, $this->re, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
				list(, $errorOffset) = end($tmp);
			}
		}

		if (isset($errorOffset)) {
			$line = $errorOffset ? substr_count($this->input, "\n", 0, $errorOffset) + 1 : 1;
			$col = $errorOffset - strrpos(substr($this->input, 0, $errorOffset), "\n") + 1;
			$token = str_replace("\n", '\n', substr($input, $errorOffset, 10));
			throw new TokenizerException("Unexpected '$token' on line $line, column $col.");
		}
		return $this;
	}



	function getIterator()
	{
		return new \ArrayIterator($this->tokens);
	}



	function nextToken($i)
	{
		while (isset($this->tokens[++$i])) {
			$name = $this->tokens[$i][1];
			if ($name !== self::T_WHITESPACE && $name !== self::T_COMMENT) {
				return $this->tokens[$i][0];
			}
		}
	}



	public function getOffset($i)
	{
		$tokens = String::split($this->input, $this->re, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
		list(, $offset) = $tokens[$i];
		return array(
			$offset,
			($offset ? substr_count($this->input, "\n", 0, $offset) + 1 : 1),
			$offset - strrpos(substr($this->input, 0, $offset), "\n"),
		);
	}

}



/**
 * The exception that indicates tokenizer error.
 */
class TokenizerException extends \Exception
{
}
