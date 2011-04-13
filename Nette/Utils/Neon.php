<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Utils;

use Nette;



/**
 * Simple parser & generator for Nette Object Notation.
 *
 * @author     David Grudl
 */
class Neon extends Nette\Object
{
	const BLOCK = 1;

	/** @var array */
	private static $patterns = array(
		'\'[^\'\n]*\'|"(?:\\\\.|[^"\\\\\n])*"', // string
		'@[a-zA-Z_0-9\\\\]+', // object
		'[:-](?=\s|$)|[,=[\]{}()]', // symbol
		'?:#.*', // comment
		'\n[\t ]*', // new line + indent
		'[^#"\',:=@[\]{}()<>\x00-\x20!`](?:[^#,:=\]})>\x00-\x1F]+|:(?!\s|$)|(?<!\s)#)*(?<!\s)', // literal / boolean / integer / float
		'?:[\t ]+', // whitespace
	);

	/** @var Tokenizer */
	private static $tokenizer;

	private static $brackets = array(
		'[' => ']',
		'{' => '}',
		'(' => ')',
	);

	/** @var int */
	private $n = 0;

	/** @var bool */
	private $indentTabs;


	/**
	 * Returns the NEON representation of a value.
	 * @param  mixed
	 * @param  int
	 * @return string
	 */
	public static function encode($var, $options = NULL)
	{
		if ($var instanceof \DateTime) {
			return $var->format('Y-m-d H:i:s O');
		}
		if (is_object($var)) {
			$obj = $var; $var = array();
			foreach ($obj as $k => $v) {
				$var[$k] = $v;
			}
		}
		if (is_array($var)) {
			$isArray = array_keys($var) === range(0, count($var) - 1);
			$s = '';
			if ($options & self::BLOCK) {
				foreach ($var as $k => $v) {
					$v = self::encode($v, self::BLOCK);
					$s .= ($isArray ? '-' : self::encode($k) . ':')
						. (strpos($v, "\n") === FALSE ? ' ' . $v : "\n\t" . str_replace("\n", "\n\t", $v))
						. "\n";
					continue;
				}
				return $s;

			} else {
				foreach ($var as $k => $v) {
					$s .= ($isArray ? '' : self::encode($k) . ': ') . self::encode($v) . ', ';
				}
				return ($isArray ? '[' : '{') . substr($s, 0, -2) . ($isArray ? ']' : '}');
			}

		} elseif (is_string($var) && !is_numeric($var)
			&& !preg_match('~[\x00-\x1F]|^\d{4}|^(true|false|yes|no|on|off|null)$~i', $var)
			&& preg_match('~^' . self::$patterns[5] . '$~', $var)
		) {
			return $var;

		} else {
			return json_encode($var);
		}
	}



	/**
	 * Decodes a NEON string.
	 * @param  string
	 * @return mixed
	 */
	public static function decode($input)
	{
		if (!is_string($input)) {
			throw new \InvalidArgumentException("Argument must be a string, " . gettype($input) . " given.");
		}
		if (!self::$tokenizer) {
			self::$tokenizer = new Tokenizer(self::$patterns, 'mi');
		}

		$input = str_replace("\r", '', $input);
		self::$tokenizer->tokenize($input);

		$parser = new self;
		$res = $parser->parse(0);

		while (isset(self::$tokenizer->tokens[$parser->n])) {
			if (self::$tokenizer->tokens[$parser->n][0] === "\n") {
				$parser->n++;
			} else {
				$parser->error();
			}
		}
		return $res;
	}



	/**
	 * @param  int  indentation (for block-parser)
	 * @param  mixed
	 * @return array
	 */
	private function parse($indent = NULL, $result = NULL)
	{
		$inlineParser = $indent === NULL;
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
				if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
					$this->error('Unacceptable key');
				} else {
					$key = (string) $value;
				}
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
				$endBracket = self::$brackets[$tokens[$n++]];
				$hasValue = TRUE;
				$value = $this->parse(NULL, array());
				if (!isset($tokens[$n]) || $tokens[$n] !== $endBracket) { // unexpected type of bracket or block-parser
					$this->error();
				}

			} elseif ($t === ']' || $t === '}' || $t === ')') { // Closing bracket ] ) }
				if (!$inlineParser) {
					$this->error();
				}
				break;

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
					if (!isset($tokens[$n+1])) break;

					$newIndent = strlen($tokens[$n]) - 1;
					if ($indent === NULL) { // first iteration
						$indent = $newIndent;
					}
					if ($newIndent) {
						if ($this->indentTabs === NULL) {
							$this->indentTabs = $tokens[$n][1] === "\t";
						}
						if (strpos($tokens[$n], $this->indentTabs ? ' ' : "\t")) {
							$this->error('Either tabs or spaces may be used as indenting chars, but not both.');
						}
					}

					if ($newIndent > $indent) { // open new block-array or hash
						if ($hasValue || !$hasKey) {
							$n++;
							$this->error('Unexpected indentation.');
						} elseif ($key === NULL) {
							$result[] = $this->parse($newIndent);
						} else {
							$result[$key] = $this->parse($newIndent);
						}
						$newIndent = isset($tokens[$n]) ? strlen($tokens[$n]) - 1 : 0;
						$hasKey = FALSE;

					} else {
						if ($hasValue && !$hasKey) { // block items must have "key"; NULL key means list item
							break;

						} elseif ($hasKey) {
							$value = $hasValue ? $value : NULL;
							if ($key === NULL) $result[] = $value; else $result[$key] = $value;
							$hasKey = $hasValue = FALSE;
						}
					}

					if ($newIndent < $indent) { // close block
						return $result; // block parser exit point
					}
				}

			} else { // Value
				if ($hasValue) {
					$this->error();
				}
				static $consts = array(
					'true' => TRUE, 'True' => TRUE, 'TRUE' => TRUE, 'yes' => TRUE, 'Yes' => TRUE, 'YES' => TRUE, 'on' => TRUE, 'On' => TRUE, 'ON' => TRUE,
					'false' => FALSE, 'False' => FALSE, 'FALSE' => FALSE, 'no' => FALSE, 'No' => FALSE, 'NO' => FALSE, 'off' => FALSE, 'Off' => FALSE, 'OFF' => FALSE,
				);
				if ($t[0] === '"') {
					$value = preg_replace_callback('#\\\\(?:u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', array($this, 'cbString'), substr($t, 1, -1));
				} elseif ($t[0] === "'") {
					$value = substr($t, 1, -1);
				} elseif (isset($consts[$t])) {
					$value = $consts[$t];
				} elseif ($t === 'null' || $t === 'Null' || $t === 'NULL') {
					$value = NULL;
				} elseif (is_numeric($t)) {
					$value = $t * 1;
				} elseif (preg_match('#\d\d\d\d-\d\d?-\d\d?(?:(?:[Tt]| +)\d\d?:\d\d:\d\d(?:\.\d*)? *(?:Z|[-+]\d\d?(?::\d\d)?)?)?$#A', $t)) {
					$value = new Nette\DateTime($t);
				} else { // literal
					$value = $t;
				}
				$hasValue = TRUE;
			}
		}

		if ($inlineParser) {
			if ($hasValue) {
				if ($hasKey) $result[$key] = $value; else $result[] = $value;
			} elseif ($hasKey) {
				$this->error();
			}
		} else {
			if ($hasValue && !$hasKey) { // block items must have "key"
				if ($result === NULL) {
					$result = $value; // simple value parser
				} else {
					$this->error();
				}
			} elseif ($hasKey) {
				$value = $hasValue ? $value : NULL;
				if ($key === NULL) $result[] = $value; else $result[$key] = $value;
			}
		}
		return $result;
	}



	private function cbString($m)
	{
		static $mapping = array('t' => "\t", 'n' => "\n", '"' => '"', '\\' => '\\',  '/' => '/', '_' => "\xc2\xa0");
		$sq = $m[0];
		if (isset($mapping[$sq[1]])) {
			return $mapping[$sq[1]];
		} elseif ($sq[1] === 'u' && strlen($sq) === 6) {
			return Nette\StringUtils::chr(hexdec(substr($sq, 2)));
		} elseif ($sq[1] === 'x' && strlen($sq) === 4) {
			return chr(hexdec(substr($sq, 2)));
		} else {
			$this->error("Invalid escaping sequence $sq");
		}
	}



	private function error($message = "Unexpected '%s'")
	{
		list(, $line, $col) = self::$tokenizer->getOffset($this->n);
		$token = isset(self::$tokenizer->tokens[$this->n])
			? str_replace("\n", '<new line>', Nette\StringUtils::truncate(self::$tokenizer->tokens[$this->n], 40))
			: 'end';
		throw new NeonException(str_replace('%s', $token, $message) . " on line $line, column $col.");
	}

}



/**
 * The exception that indicates error of NEON decoding.
 */
class NeonException extends \Exception
{
}
