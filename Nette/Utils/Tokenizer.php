<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Simple lexical analyser. Internal class.
 *
 * @author     David Grudl
 * @internal
 */
class Tokenizer extends Nette\Object
{
	const VALUE = 0,
		OFFSET = 1,
		TYPE = 2;

	/** @var string */
	private $re;

	/** @var array */
	private $types;


	/**
	 * @param  array of [(int) symbol type => pattern]
	 * @param  string  regular expression flag
	 */
	public function __construct(array $patterns, $flags = '')
	{
		$this->re = '~(' . implode(')|(', $patterns) . ')~A' . $flags;
		$keys = array_keys($patterns);
		$this->types = $keys === range(0, count($patterns) - 1) ? FALSE : $keys;
	}


	/**
	 * Tokenize string.
	 * @param  string
	 * @return array
	 */
	public function tokenize($input)
	{
		if ($this->types) {
			$tokens = Strings::matchAll($input, $this->re);
			$len = 0;
			$count = count($this->types);
			foreach ($tokens as & $match) {
				$type = NULL;
				for ($i = 1; $i <= $count; $i++) {
					if (!isset($match[$i])) {
						break;
					} elseif ($match[$i] != NULL) {
						$type = $this->types[$i - 1]; break;
					}
				}
				$match = array(self::VALUE => $match[0], self::OFFSET => $len, self::TYPE => $type);
				$len += strlen($match[self::VALUE]);
			}
			if ($len !== strlen($input)) {
				$errorOffset = $len;
			}

		} else {
			$tokens = Strings::split($input, $this->re, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
			$last = end($tokens);
			if ($tokens && !Strings::match($last[0], $this->re)) {
				$errorOffset = $last[1];
			}
		}

		if (isset($errorOffset)) {
			list($line, $col) = $this->getCoordinates($input, $errorOffset);
			$token = str_replace("\n", '\n', substr($input, $errorOffset, 10));
			throw new TokenizerException("Unexpected '$token' on line $line, column $col.");
		}
		return $tokens;
	}


	/**
	 * Returns position of token in input string.
	 * @param  int token number
	 * @return array [line, column]
	 */
	public static function getCoordinates($text, $offset)
	{
		$text = substr($text, 0, $offset);
		return array(substr_count($text, "\n") + 1, $offset - strrpos("\n" . $text, "\n") + 1);
	}

}


/**
 * The exception that indicates tokenizer error.
 */
class TokenizerException extends \Exception
{
}
