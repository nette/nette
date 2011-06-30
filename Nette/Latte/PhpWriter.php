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

	/** @var array */
	private $context;



	public static function using(MacroNode $node, $context = NULL)
	{
		return new static($node->tokenizer, $node->modifiers, $context);
	}



	public function __construct(MacroTokenizer $argsTokenizer, $modifiers = NULL, $context = NULL)
	{
		$this->argsTokenizer = $argsTokenizer;
		$this->modifiers = $modifiers;
		$this->context = $context;
	}



	/**
	 * Expands %node.word, %node.array, %node.args, %escape, %modify, %var in code.
	 * @param  string
	 * @return string
	 */
	public function write($mask)
	{
		$args = func_get_args();
		array_shift($args);
		$word = strpos($mask, '%node.word') === FALSE ? NULL : $this->argsTokenizer->fetchWord();
		$me = $this;
		$mask = Nette\Utils\Strings::replace($mask, '#%escape\(((?>[^()]+)|\((?1)\))*\)#', /*5.2* callback(*/function($m) use ($me) {
			return $me->escape(substr($m[0], 8, -1));
		}/*5.2* )*/);

		return Nette\Utils\Strings::replace($mask, '#([,+]\s*)?%(node\.word|node\.array|node\.args|modify|var)(\?)?(\s*\+\s*)?()#',
			/*5.2* callback(*/function($m) use ($me, $word, & $args) {
			list(, $l, $macro, $cond, $r) = $m;

			switch ($macro) {
			case 'node.word':
				$code = $me->formatWord($word); break;
			case 'node.args':
				$code = $me->formatArgs(); break;
			case 'node.array':
				$code = $me->formatArray();
				$code = $cond && $code === 'array()' ? '' : $code; break;
			case 'modify':
				$code = $me->formatModifiers(array_shift($args)); break;
			case 'var':
				$code = var_export(array_shift($args), TRUE); break;
			}

			if ($cond && $code === '') {
				return $r ? $l : $r;
			} else {
				return $l . $code . $r;
			}
		}/*5.2* )*/);
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
					if ($this->context && $token['value'] === 'escape') {
						$var = $this->escape($var);
						$tokenizer->fetch('|');
					} else {
						$var = "\$template->" . $token['value'] . "($var";
						$inside = TRUE;
					}
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
	public function formatArray()
	{
		$out = '';
		$expand = NULL;
		$tokenizer = $this->preprocess();
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
			return "array($out)";
		} else {
			return "array_merge(array($out" . ($expand ? ', array(' : '') ."))";
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



	public function escape($s)
	{
		switch ($this->context[0]) {
		case Parser::CONTEXT_TEXT:
			return "Nette\\Templating\\DefaultHelpers::escapeHtml($s, ENT_NOQUOTES)";
		case Parser::CONTEXT_TAG:
			return "Nette\\Templating\\DefaultHelpers::escapeHtml($s)";
		case Parser::CONTEXT_ATTRIBUTE:
			list(, $name, $quote) = $this->context;
			$quote = $quote === '"' ? '' : ', ENT_QUOTES';
			if (strncasecmp($name, 'on', 2) === 0) {
				return "htmlSpecialChars(Nette\\Templating\\DefaultHelpers::escapeJs($s)$quote)";
			} elseif ($name === 'style') {
				return "htmlSpecialChars(Nette\\Templating\\DefaultHelpers::escapeCss($s)$quote)";
			} else {
				return "htmlSpecialChars($s$quote)";
			}
		case Parser::CONTEXT_COMMENT:
			return "Nette\\Templating\\DefaultHelpers::escapeHtmlComment($s)";
		case Parser::CONTEXT_CDATA;
			return 'Nette\Templating\DefaultHelpers::escape' . ucfirst($this->context[1]) . "($s)"; // Js, Css
		case Parser::CONTEXT_NONE:
			switch (isset($this->context[1]) ? $this->context[1] : NULL) {
			case 'xml':
			case 'js':
			case 'css':
				return 'Nette\Templating\DefaultHelpers::escape' . ucfirst($this->context[1]) . "($s)";
			case 'text':
				return $s;
			default:
				return "\$template->escape($s)";
			}
		}
	}

}
