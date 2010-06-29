<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

namespace Nette;

use Nette;



/**
 * Simple parser for Nette Object Notation.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class NeonParser extends Object
{
	/** @var array */
	private static $patterns = array(
		'(\'[^\'\n]*\'|"(?:\\\\.|[^"\\\\\n])*")', // string
		'(@[a-zA-Z_0-9\\\\]+)', // object
		'([:-](?=\s|$)|[,=[\]{}()])', // symbol
		'#.*', // comment
		'(\n *)', // indent
		'literal' => '([^#"\',:=@[\]{}()<>\s](?:[^#,:=\]})>\n]+|:(?!\s)|(?<!\s)#)*)(?<!\s)', // literal / boolean / integer / float
		' +', // whitespace
	);

	/** @var string */
	private static $regexp;

	private static $brackets = array(
		'[' => ']',
		'{' => '}',
		'(' => ')',
	);

	/** @var string */
	private $input;

	/** @var array */
	private $tokens;

	/** @var int */
	private $n;



	/**
	 * Parser.
	 * @param  string
	 * @return array
	 */
	public function parse($s)
	{
		$this->tokenize($s);
		$this->n = 0;
		$res = $this->_parse();

		while (isset($this->tokens[$this->n])) {
			if ($this->tokens[$this->n][0] === "\n") $this->n++; else $this->error();
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
		$tokens = $this->tokens;
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

		throw new \Exception('NEON parse error: unexpected end of file.');
	}



	/**
	 * Lexical scanner.
	 * @param  string
	 * @return void
	 */
	private function tokenize($s)
	{
		if (!self::$regexp) {
			self::$regexp = '~' . implode('|', self::$patterns) . '~mA';
		}

		$s = str_replace("\r", '', $s);
		$s = strtr($s, "\t", ' ');
		$s = "\n" . $s . "\n"; // first is required by "Indent", last is required by parse-error check

		$this->input = $s;
		$this->tokens = String::split($s, self::$regexp, PREG_SPLIT_NO_EMPTY);

		if (end($this->tokens) !== "\n") { // unable to parse
			$this->n = key($this->tokens);
			$this->error();
		}
	}



	private function error()
	{
		$tokens = String::split($this->input, self::$regexp, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
		list($token, $offset) = $tokens[$this->n];
		$line = substr_count($this->input, "\n", 0, $offset) + 1;
		$col = $offset - strrpos(substr($this->input, 0, $offset), "\n");
		throw new \Exception('NEON parse error: unexpected ' . str_replace("\n", '\n', substr($token, 0, 10))  . " on line $line, column $col.");
	}

}
