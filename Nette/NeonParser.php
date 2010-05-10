<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 */

/*namespace Nette;*/



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
		'(:(?=\s|$)|[,=[\]{}()])', // symbol
		'#.*', // comment
		'(\n *)(-(?=\s|$))?', // block-array | block-hash
		'([^#"\',:=@[\]{}()<>\s](?:[^#,:=\]})>\n]+|:(?!\s)|(?<!\s)#)*)(?<!\s)', // literal / boolean / integer / float
		' +', // whitespace
	);

	/** @var string */
	private static $regexp;

	const T_KEY_VALUE_SEPARATOR = 1;
	const T_SEPARATOR = 2;
	const T_BRACKET_OPEN = 3;
	const T_BRACKET_CLOSE = 4;
	const T_BULLET = 7;
	const T_VALUE = 8;
	const T_OBJECT = 9;
	const T_INDENT = 10;

	/** @var array */
	private static $symbols = array(
		'=' => self::T_KEY_VALUE_SEPARATOR,
		':' => self::T_KEY_VALUE_SEPARATOR,
		',' => self::T_SEPARATOR,
		'[' => self::T_BRACKET_OPEN,
		']' => self::T_BRACKET_CLOSE,
		'{' => self::T_BRACKET_OPEN,
		'}' => self::T_BRACKET_CLOSE,
		'(' => self::T_BRACKET_OPEN,
		')' => self::T_BRACKET_CLOSE,
		'-' => self::T_BULLET,
	);

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
		return $this->parseBlock();
	}



	private function parseBlock($indent = 0)
	{
		$result = array();
		$value = $key = $object = NULL;
		$hasValue = $hasKey = FALSE;
		$tokens = $this->tokens;
		$n = & $this->n;
		$count = count($tokens);

		for (; $n < $count; $n++) {
			list($t, $origT) = $tokens[$n];

			if ($t === self::T_VALUE) {
				if ($hasValue) {
					$this->error();
				}
				$value = $origT;
				$hasValue = TRUE;

			} elseif ($t === self::T_SEPARATOR) {
				if (!$hasValue) {
					$this->error();
				}
				if ($hasKey) $result[$key] = $value; else $result[] = $value;
				$hasKey = $hasValue = FALSE;

			} elseif ($t === self::T_KEY_VALUE_SEPARATOR) {
				if ($hasKey) {
					$this->error();
				}
				$key = $value;
				$hasKey = TRUE;
				$hasValue = FALSE;

			} elseif ($t === self::T_BRACKET_OPEN) {
				if ($hasValue) {
					$this->error();
				}
				$hasValue = TRUE;
				$value = $this->parseArray();

			} elseif ($t === self::T_BRACKET_CLOSE) {
				$this->error();

			} elseif ($t === self::T_OBJECT) {
				$object = $origT; // TODO

			} elseif ($t === self::T_INDENT) { // indent
				if (isset($tokens[$n+1][0]) && $tokens[$n+1][0] === self::T_INDENT) {
					continue;

				} else {
					if ($indent === $origT) {
						if ($hasKey) {
							$result[$key] = $hasValue ? $value : NULL;
						} elseif ($hasValue) {
							$result[] = $value;
						}
						$hasKey = $hasValue = FALSE;

					} elseif ($indent < $origT) { // open new block-array or hash
						if ($hasValue) {
							$this->error();
						}
						if ($hasKey) {
							$result[$key] = $this->parseBlock($origT);
						} elseif ($hasValue) {
							$result[] = $this->parseBlock($origT);
						}
						$hasKey = $hasValue = FALSE;

					} else { // close block
						break;
					}
				}
			}
		}

		// flush last item
		if ($hasKey) {
			$result[$key] = $hasValue ? $value : NULL;
		} elseif ($hasValue) {
			$result[] = $value;
		}

		return $result;
	}



	private function parseArray()
	{
		$result = array();
		$value = $key = $object = NULL;
		$hasValue = $hasKey = FALSE;
		$tokens = $this->tokens;
		$n = & $this->n;
		$count = count($tokens);

		$pairs = array(
			'[' => ']',
			'{' => '}',
			'(' => ')',
		);
		$end = $pairs[$tokens[$n++][1]];

		for (; $n < $count; $n++) {
			list($t, $origT) = $tokens[$n];

			if ($t === self::T_VALUE) {
				if ($hasValue) {
					$this->error();
				}
				$value = $origT;
				$hasValue = TRUE;

			} elseif ($t === self::T_SEPARATOR) {
				if (!$hasValue) {
					$this->error();
				}
				if ($hasKey) $result[$key] = $value; else $result[] = $value;
				$hasKey = $hasValue = FALSE;

			} elseif ($t === self::T_KEY_VALUE_SEPARATOR) {
				if ($hasKey) {
					$this->error();
				}
				$key = $value;
				$hasKey = TRUE;
				$hasValue = FALSE;

			} elseif ($t === self::T_BRACKET_OPEN) {
				if ($hasValue) {
					$this->error();
				}
				$hasValue = TRUE;
				$value = $this->parseArray();

			} elseif ($t === self::T_BRACKET_CLOSE) {
				if ($hasValue) {
					if ($hasKey) $result[$key] = $value; else $result[] = $value;
				} elseif ($hasKey) {
					$this->error();
				}

				if ($origT !== $end) {
					$this->error();
				}
				return $result;

			} elseif ($t === self::T_OBJECT) {
				$object = $origT;

			} elseif ($t === self::T_INDENT) { // indent
				if ($hasValue) {
					if ($hasKey) $result[$key] = $value; else $result[] = $value;
					$hasKey = $hasValue = FALSE;
				}
			}
		}

		$this->error(); // Unexpected end
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
		$s = "\n" . $s; // required by block-array & block-hash

		$matches = preg_split(self::$regexp, $s, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		if (count($matches) === 1) { // parse error
			preg_match_all(self::$regexp, $s, $matches, PREG_SET_ORDER);
			$len = 0;
			foreach ($matches as $m) $len += strlen($m[0]);
			$line = substr_count($s, "\n", 0, $len);
			$col = $len - strrpos(substr($s, 0, $len), "\n");
			throw new /*\*/Exception("Parse error on line $line, column $col");
		}

		// tokenize
		$tokens = array();
		foreach ($matches as $m) {
			$ch = $m[0];
			if (isset(self::$symbols[$m])) {
				$tokens[] = array(self::$symbols[$m], $m);
			} elseif ($ch === '"') {
				$tokens[] = array(self::T_VALUE, json_decode($m));
			} elseif ($ch === "'") {
				$tokens[] = array(self::T_VALUE, substr($m, 1, -1));
			} elseif (is_numeric($m)) {
				$tokens[] = array(self::T_VALUE, $m * 1);
			} elseif ($ch === '@') {
				$tokens[] = array(self::T_OBJECT, $m);
			} elseif ($ch === "\n") {
				$tokens[] = array(self::T_INDENT, strlen($m) - 1);
			} elseif (($ml = strtolower($m)) === 'true' || $ml === 'yes') {
				$tokens[] = array(self::T_VALUE, TRUE);
			} elseif ($ml === 'false' || $ml === 'no') {
				$tokens[] = array(self::T_VALUE, FALSE);
			} elseif ($ml === 'null') {
				$tokens[] = array(self::T_VALUE, NULL);
			} else { // literal
				$tokens[] = array(self::T_VALUE, $m);
			}
		}
		$this->tokens = $tokens;
	}



	private function error()
	{
		throw new /*\*/Exception('Parse error');
	}

}
