<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette,
	Nette\Utils\Strings;


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
		$this->types = array_keys($patterns);
	}


	/**
	 * Tokenize string.
	 * @param  string
	 * @return array
	 */
	public function tokenize($input)
	{
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
			list($line, $col) = $this->getCoordinates($input, $len);
			$token = str_replace("\n", '\n', substr($input, $len, 10));
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
