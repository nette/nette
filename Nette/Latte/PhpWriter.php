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
	/** @var MacroTokenizer */
	private $argsTokenizer;

	/** @var string */
	private $modifiers;

	/** @var string */
	private $escape;



	public static function using(MacroNode $node, $escape = NULL)
	{
		return new static($node->tokenizer, $node->modifiers, $escape);
	}



	public function __construct(MacroTokenizer $argsTokenizer, $modifiers = NULL, $escape = NULL)
	{
		$this->argsTokenizer = $argsTokenizer;
		$this->modifiers = $modifiers;
		$this->escape = explode('|', $escape);
	}



	/**
	 * Formats modifiers calling.
	 * @param  string
	 * @return string
	 */
	public function formatModifiers($var)
	{
		$modifiers = ltrim($this->modifiers, '|');
		if (!$modifiers) {
			return $var;
		}

		$tokenizer = $this->preprocess(new MacroTokenizer($modifiers));
		$inside = FALSE;
		while ($token = $tokenizer->fetchToken()) {
			if ($token['type'] === MacroTokenizer::T_WHITESPACE) {
				$var = rtrim($var) . ' ';

			} elseif (!$inside) {
				if ($token['type'] === MacroTokenizer::T_SYMBOL) {
					if ($this->escape && $token['value'] === 'escape') {
						$var = $this->escape[0] . "($var" . (isset($this->escape[1]) ? ', ' . var_export($this->escape[1], TRUE) : '');
					} else {
						$var = "\$template->" . $token['value'] . "($var";
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
					$var .= $this->canQuote($tokenizer) ? "'$token[value]'" : $token['value'];
				}
			}
		}
		return $inside ? "$var)" : $var;
	}



	/**
	 * Formats macro arguments to PHP code.
	 * @return string
	 */
	public function formatArgs()
	{
		$out = '';
		$tokenizer = $this->preprocess();
		while ($token = $tokenizer->fetchToken()) {
			$out .= $this->canQuote($tokenizer) ? "'$token[value]'" : $token['value'];
		}
		return $out;
	}



	/**
	 * Formats macro arguments to PHP array.
	 * @return string
	 */
	public function formatArray($prefix = '')
	{
		$out = '';
		$expand = NULL;
		$tokenizer = $this->preprocess();
		if (!$tokenizer->tokens) {
			return '';
		}
		while ($token = $tokenizer->fetchToken()) {
			if ($token['value'] === '(expand)' && $token['depth'] === 0) {
				$expand = TRUE;
				$out .= '),';

			} elseif ($expand && ($token['value'] === ',') && !$token['depth']) {
				$expand = FALSE;
				$out .= ', array(';
			} else {
				$out .= $this->canQuote($tokenizer) ? "'$token[value]'" : $token['value'];
			}
		}
		if ($expand === NULL) {
			return $prefix . "array($out)";
		} else {
			return $prefix . "array_merge(array($out" . ($expand ? ', array(' : '') ."))";
		}
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public function formatWord($s)
	{
		return (is_numeric($s) || strspn($s, '\'"$') || in_array(strtolower($s), array('true', 'false', 'null')))
			? $s : '"' . $s . '"';
	}



	/**
	 * @return bool
	 */
	public function canQuote($tokenizer)
	{
		return $tokenizer->isCurrent(MacroTokenizer::T_SYMBOL)
			&& (!$tokenizer->hasPrev() || $tokenizer->isPrev(',', '(', '[', '=', '=>', ':', '?'))
			&& (!$tokenizer->hasNext() || $tokenizer->isNext(',', ')', ']', '=', '=>', ':', '|'));
	}



	/**
	 * Preprocessor for tokens.
	 * @return MacroTokenizer
	 */
	public function preprocess(MacroTokenizer $tokenizer = NULL)
	{
		$tokenizer = $tokenizer === NULL ? $this->argsTokenizer : $tokenizer;
		$inTernary = $prev = NULL;
		$tokens = $arrays = array();
		while ($token = $tokenizer->fetchToken()) {
			$token['depth'] = $depth = count($arrays);

			if ($token['type'] === MacroTokenizer::T_COMMENT) {
				continue; // remove comments

			} elseif ($token['type'] === MacroTokenizer::T_WHITESPACE) {
				$tokens[] = $token;
				continue;
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

		if ($inTernary !== NULL) { // close ternary
			$tokens[] = MacroTokenizer::createToken(':') + array('depth' => count($arrays));
			$tokens[] = MacroTokenizer::createToken('null') + array('depth' => count($arrays));
		}

		$tokenizer = clone $tokenizer;
		$tokenizer->position = 0;
		$tokenizer->tokens = $tokens;
		return $tokenizer;
	}

}
