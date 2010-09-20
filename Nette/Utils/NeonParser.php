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
 * Simple parser for Nette Object Notation.
 *
 * @author     David Grudl
 */
class NeonParser extends Object
{
	/** @var array */
	private static $patterns = array(
		'\'[^\'\n]*\'|"(?:\\\\.|[^"\\\\\n])*"', // string
		'@[a-zA-Z_0-9\\\\]+', // object
		'[:-](?=\s|$)|[,=[\]{}()]', // symbol
		'?:#.*', // comment
		'\n *', // indent
		'[^#"\',:=@[\]{}()<>\s](?:[^#,:=\]})>\n]+|:(?!\s)|(?<!\s)#)*(?<!\s)', // literal / boolean / integer / float
		'?: +', // whitespace
	);

	/** @var Tokenizer */
	private static $tokenizer;

	private static $brackets = array(
		'[' => ']',
		'{' => '}',
		'(' => ')',
	);

	/** @var int */
	private $n;



	/**
	 * Parser.
	 * @param  string
	 * @return array
	 */
	public function parse($input)
	{
		if (!self::$tokenizer) { // speed-up
			self::$tokenizer = new Tokenizer(self::$patterns, 'mi');
		}
		$input = str_replace("\r", '', $input);
		$input = strtr($input, "\t", ' ');
		$input = "\n" . $input . "\n"; // first \n is required by "Indent"
		self::$tokenizer->tokenize($input);

		$this->n = 0;
		$res = $this->_parse();

		while (isset(self::$tokenizer->tokens[$this->n])) {
			if (self::$tokenizer->tokens[$this->n][0] === "\n") {
				$this->n++;
			} else {
				$this->error();
			}
		}
		return $res;
	}



	/**
	 * Tokenizer & parser.
	 * @param  int  indentation (for block-parser)
	 * @param  string  end char (for inline-hash/array parser)
	 * @return array
	 */
	private function _parse($indent = NULL, $endBracket = NULL)
	{
		$inlineParser = $endBracket !== NULL; // block or inline parser?

		$result = $inlineParser || $indent ? array() : NULL;
		$value = $key = $object = NULL;
		$hasValue = $hasKey = FALSE;
		$tokens = self::$tokenizer->tokens;
		$n = & $this->n;
		$count = count($tokens);

		for (; $n < $count; $n++) {
			$t = $tokens[$n];

			if ($t === ',') { // ArrayEntry separator
				if (!$hasValue || !$inlineParser) {
					$this->error();
				}
				if ($hasKey) $result[$key] = $value; else $result[] = $value;
				$hasKey = $hasValue = FALSE;

			} elseif ($t === ':' || $t === '=') { // KeyValuePair separator
				if ($hasKey || !$hasValue) {
					$this->error();
				}
				$key = (string) $value;
				$hasKey = TRUE;
				$hasValue = FALSE;

			} elseif ($t === '-') { // BlockArray bullet
				if ($hasKey || $hasValue || $inlineParser) {
					$this->error();
				}
				$key = NULL;
				$hasKey = TRUE;

			} elseif (isset(self::$brackets[$t])) { // Opening bracket [ ( {
				if ($hasValue) {
					$this->error();
				}
				$hasValue = TRUE;
				$value = $this->_parse(NULL, self::$brackets[$tokens[$n++]]);

			} elseif ($t === ']' || $t === '}' || $t === ')') { // Closing bracket ] ) }
				if ($t !== $endBracket) { // unexpected type of bracket or block-parser
					$this->error();
				}
				if ($hasValue) {
					if ($hasKey) $result[$key] = $value; else $result[] = $value;
				} elseif ($hasKey) {
					$this->error();
				}
				return $result; // inline parser exit point

			} elseif ($t[0] === '@') { // Object
				$object = $t; // TODO

			} elseif ($t[0] === "\n") { // Indent
				if ($inlineParser) {
					if ($hasValue) {
						if ($hasKey) $result[$key] = $value; else $result[] = $value;
						$hasKey = $hasValue = FALSE;
					}

				} else {
					while (isset($tokens[$n+1]) && $tokens[$n+1][0] === "\n") $n++; // skip to last indent

					$newIndent = strlen($tokens[$n]) - 1;
					if ($indent === NULL) { // first iteration
						$indent = $newIndent;
					}

					if ($newIndent > $indent) { // open new block-array or hash
						if ($hasValue || !$hasKey) {
							$this->error();
						} elseif ($key === NULL) {
							$result[] = $this->_parse($newIndent);
						} else {
							$result[$key] = $this->_parse($newIndent);
						}
						$newIndent = strlen($tokens[$n]) - 1;
						$hasKey = FALSE;

					} else {
						if ($hasValue && !$hasKey) { // block items must have "key"; NULL key means list item
							if ($result === NULL) return $value;  // simple value parser exit point
							$this->error();

						} elseif ($hasKey) {
							$value = $hasValue ? $value : NULL;
							if ($key === NULL) $result[] = $value; else $result[$key] = $value;
							$hasKey = $hasValue = FALSE;
						}
					}

					if ($newIndent < $indent || !isset($tokens[$n+1])) { // close block
						return $result; // block parser exit point
					}
				}

			} else { // Value
				if ($hasValue) {
					$this->error();
				}
				if ($t[0] === '"') {
					$value = json_decode($t);
					if ($value === NULL) {
						$this->error();
					}
				} elseif ($t[0] === "'") {
					$value = substr($t, 1, -1);
				} elseif ($t === 'true' || $t === 'yes' || $t === 'TRUE' || $t === 'YES') {
					$value = TRUE;
				} elseif ($t === 'false' || $t === 'no' || $t === 'FALSE' || $t === 'NO') {
					$value = FALSE;
				} elseif ($t === 'null' || $t === 'NULL') {
					$value = NULL;
				} elseif (is_numeric($t)) {
					$value = $t * 1;
				} else { // literal
					$value = $t;
				}
				$hasValue = TRUE;
			}
		}

		throw new NeonException('Unexpected end of file.');
	}



	private function error()
	{
		list(, $line, $col) = self::$tokenizer->getOffset($this->n);
		throw new NeonException("Unexpected '" . str_replace("\n", '\n', substr(self::$tokenizer->tokens[$this->n], 0, 10))
			. "' on line " . ($line - 1) . ", column $col.");
	}

}



/**
 * The exception that indicates error of NEON decoding.
 */
class NeonException extends \Exception
{
}
