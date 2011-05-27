<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette;



/**
 * PHP code generator helpers.
 *
 * @author     David Grudl
 */
class PhpWriter extends Nette\Object
{


	/**
	 * Applies modifiers.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatModifiers($var, $modifiers, $escape = NULL)
	{
		if (!$modifiers) {
			return $var;
		}
		$inside = FALSE;
		foreach ($this->parseMacroArgs(ltrim($modifiers, '|')) as $token) {
			if ($token['type'] === MacroTokenizer::T_WHITESPACE) {
				$var = rtrim($var) . ' ';

			} elseif (!$inside) {
				if ($token['type'] === MacroTokenizer::T_SYMBOL) {
					if ($escape && trim($token['value'], "'") === 'escape') {
						$tmp = explode('|', $escape);
						$var = $tmp[0] . "($var" . (isset($tmp[1]) ? ', ' . var_export($tmp[1], TRUE) : '');
					} else {
						$var = "\$template->" . trim($token['value'], "'") . "($var";
					}
					$inside = TRUE;
				} else {
					throw new ParseException("Modifier name must be alphanumeric string, '$token[value]' given.");
				}
			} else {
				if ($token['value'] === ':' || $token['value'] === ',') {
					$var = $var . ', ';

				} elseif ($token['value'] === '|') {
					$var = $var . ')';
					$inside = FALSE;

				} else {
					$var .= $token['value'];
				}
			}
		}
		return $inside ? "$var)" : $var;
	}



	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public function fetchToken(& $s)
	{
		if ($matches = Nette\Utils\Strings::match($s, '#^((?>'.Parser::RE_STRING.'|[^\'"\s,]+)+)\s*,?\s*(.*)$#s')) { // token [,] tail
			$s = $matches[2];
			return $matches[1];
		}
		return NULL;
	}



	/**
	 * Reformats Latte to PHP code.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatMacroArgs($input)
	{
		$out = '';
		foreach ($this->parseMacroArgs($input) as $token) {
			$out .= $token['value'];
		}
		return $out;
	}



	/**
	 * Reformats Latte to PHP array.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatArray($input, $prefix = '')
	{
		$tokens = $this->parseMacroArgs($input);
		if (!$tokens) {
			return '';
		}
		$out = '';
		$expand = NULL;
		$tokens[] = NULL; // sentinel
		foreach ($tokens as $token) {
			if ($token['value'] === '(expand)' && $token['depth'] === 0) {
				$expand = TRUE;
				$out .= '),';

			} elseif ($expand && ($token['value'] === ',' || $token['value'] === NULL) && !$token['depth']) {
				$expand = FALSE;
				$out .= ', array(';
			} else {
				$out .= $token['value'];
			}
		}
		return $prefix . ($expand === NULL ? "array($out)" : "array_merge(array($out))");
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public function formatString($s)
	{
		static $keywords = array('true'=>1, 'false'=>1, 'null'=>1);
		return (is_numeric($s) || strspn($s, '\'"$') || isset($keywords[strtolower($s)])) ? $s : '"' . $s . '"';
	}



	/**
	 * Tokenizer and preparser for macro arguments.
	 * @param  string
	 * @return array
	 */
	public function parseMacroArgs($input)
	{
		$tokenizer = new MacroTokenizer($input);

		$inTernary = $lastSymbol = $prev = NULL;
		$tokens = $arrays = array();
		$n = -1;
		while (++$n < count($tokenizer->tokens)) {
			$token = $tokenizer->tokens[$n];
			$token['depth'] = $depth = count($arrays);

			if ($token['type'] === MacroTokenizer::T_COMMENT) {
				continue; // remove comments

			} elseif ($token['type'] === MacroTokenizer::T_WHITESPACE) {
				$tokens[] = $token;
				continue;

			} elseif ($token['type'] === MacroTokenizer::T_SYMBOL && ($prev === NULL || in_array($prev['value'], array(',', '(', '[', '=', '=>', ':', '?')))) {
				$lastSymbol = count($tokens); // quoting pre-requirements

			} elseif (is_int($lastSymbol) && in_array($token['value'], array(',', ')', ']', '=', '=>', ':', '|'))) {
				$tokens[$lastSymbol]['value'] = "'" . $tokens[$lastSymbol]['value'] . "'"; // quote symbols
				$lastSymbol = NULL;

			} else {
				$lastSymbol = NULL;
			}

			if ($token['value'] === '?') { // short ternary operators without :
				$inTernary = $depth;

			} elseif ($token['value'] === ':') {
				$inTernary = NULL;

			} elseif ($inTernary === $depth && ($token['value'] === ',' || $token['value'] === ')' || $token['value'] === ']')) { // close ternary
				$tokens[] = MacroTokenizer::createToken(':') + array('depth' => $depth);
				$tokens[] = MacroTokenizer::createToken('null') + array('depth' => $depth);
				$inTernary = NULL;
			}

			if ($token['value'] === '[') { // simplified array syntax [...]
				if ($arrays[] = $prev['value'] !== ']' && $prev['type'] !== MacroTokenizer::T_SYMBOL && $prev['type'] !== MacroTokenizer::T_VARIABLE) {
					$tokens[] = MacroTokenizer::createToken('array') + array('depth' => $depth);
					$token = MacroTokenizer::createToken('(');
				}
			} elseif ($token['value'] === ']') {
				if (array_pop($arrays) === TRUE) {
					$token = MacroTokenizer::createToken(')');
				}
			} elseif ($token['value'] === '(') { // only count
				$arrays[] = '(';

			} elseif ($token['value'] === ')') { // only count
				array_pop($arrays);
			}

			$tokens[] = $prev = $token;
		}

		if (is_int($lastSymbol)) {
			$tokens[$lastSymbol]['value'] = "'" . $tokens[$lastSymbol]['value'] . "'"; // quote symbols
		}
		if ($inTernary !== NULL) { // close ternary
			$tokens[] = MacroTokenizer::createToken(':') + array('depth' => count($arrays));
			$tokens[] = MacroTokenizer::createToken('null') + array('depth' => count($arrays));
		}

		return $tokens;
	}

}
