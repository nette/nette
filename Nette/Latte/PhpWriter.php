<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette,
	Nette\Utils\Tokenizer;



/**
 * PHP code generator helpers.
 *
 * @author     David Grudl
 */
class PhpWriter extends Nette\Object
{
	/** @var MacroTokens */
	private $tokens;

	/** @var string */
	private $modifiers;

	/** @var Compiler */
	private $compiler;



	public static function using(MacroNode $node, Compiler $compiler = NULL)
	{
		return new static($node->tokenizer, $node->modifiers, $compiler);
	}



	public function __construct(MacroTokens $tokens, $modifiers = NULL, Compiler $compiler = NULL)
	{
		$this->tokens = $tokens;
		$this->modifiers = $modifiers;
		$this->compiler = $compiler;
	}



	/**
	 * Expands %node.word, %node.array, %node.args, %escape(), %modify(), %var, %raw, %word in code.
	 * @param  string
	 * @return string
	 */
	public function write($mask)
	{
		$me = $this;
		$mask = Nette\Utils\Strings::replace($mask, '#%escape(\(([^()]*+|(?1))+\))#', function($m) use ($me) {
			return $me->escape(substr($m[1], 1, -1));
		});
		$mask = Nette\Utils\Strings::replace($mask, '#%modify(\(([^()]*+|(?1))+\))#', function($m) use ($me) {
			return $me->formatModifiers(substr($m[1], 1, -1));
		});

		$args = func_get_args();
		$pos = $this->tokens->position;
		$word = strpos($mask, '%node.word') === FALSE ? NULL : $this->tokens->fetchWord();

		$code = Nette\Utils\Strings::replace($mask, '#([,+]\s*)?%(node\.|\d+\.|)(word|var|raw|array|args)(\?)?(\s*\+\s*)?()#',
		function($m) use ($me, $word, & $args) {
			list(, $l, $source, $format, $cond, $r) = $m;

			switch ($source) {
			case 'node.':
				$arg = $word; break;
			case '':
				$arg = next($args); break;
			default:
				$arg = $args[$source + 1]; break;
			}

			switch ($format) {
			case 'word':
				$code = $me->formatWord($arg); break;
			case 'args':
				$code = $me->formatArgs(); break; // TODO: only as node.args
			case 'array':
				$code = $me->formatArray(); // TODO: only as node.array
				$code = $cond && $code === 'array()' ? '' : $code; break;
			case 'var':
				$code = var_export($arg, TRUE); break;
			case 'raw':
				$code = (string) $arg; break;
			}

			if ($cond && $code === '') {
				return $r ? $l : $r;
			} else {
				return $l . $code . $r;
			}
		});

		$this->tokens->position = $pos;
		return $code;
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

		$tokens = $this->preprocess(new MacroTokens($modifiers));
		$inside = FALSE;
		while ($token = $tokens->nextToken()) {
			if ($tokens->isCurrent(MacroTokens::T_WHITESPACE)) {
				$var = rtrim($var) . ' ';

			} elseif (!$inside) {
				if ($tokens->isCurrent(MacroTokens::T_SYMBOL)) {
					if ($this->compiler && $tokens->isCurrent('escape')) {
						$var = $this->escape($var);
						$tokens->nextToken('|');
					} else {
						$var = "\$template->" . $token[Tokenizer::VALUE] . "($var";
						$inside = TRUE;
					}
				} else {
					throw new CompileException("Modifier name must be alphanumeric string, '" . $token[Tokenizer::VALUE] . "' given.");
				}
			} else {
				if ($tokens->isCurrent(':', ',')) {
					$var = $var . ', ';

				} elseif ($tokens->isCurrent('|')) {
					$var = $var . ')';
					$inside = FALSE;

				} else {
					$var .= $this->canQuote($tokens) ? "'" . $token[Tokenizer::VALUE] . "'" : $token[Tokenizer::VALUE];
				}
			}
		}
		return $inside ? "$var)" : $var;
	}



	/**
	 * Formats macro arguments to PHP code. (It advances tokenizer to the end as a side effect.)
	 * @return string
	 */
	public function formatArgs(MacroTokens $tokens = NULL)
	{
		$out = '';
		$tokens = $this->preprocess($tokens);
		while ($token = $tokens->nextToken()) {
			$out .= $this->canQuote($tokens) ? "'" . $token[Tokenizer::VALUE] . "'" : $token[Tokenizer::VALUE];
		}
		return $out;
	}



	/**
	 * Formats macro arguments to PHP array. (It advances tokenizer to the end as a side effect.)
	 * @return string
	 */
	public function formatArray()
	{
		$out = '';
		$expand = NULL;
		$tokens = $this->preprocess();
		while ($token = $tokens->nextToken()) {
			if ($tokens->isCurrent('(expand)') && $token['depth'] === 0) {
				$expand = TRUE;
				$out .= '),';

			} elseif ($expand && $tokens->isCurrent(',') && !$token['depth']) {
				$expand = FALSE;
				$out .= ', array(';
			} else {
				$out .= $this->canQuote($tokens) ? "'" . $token[Tokenizer::VALUE] . "'" : $token[Tokenizer::VALUE];
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
		return (is_numeric($s) || preg_match('#^\$|[\'"]|^true\z|^false\z|^null\z#i', $s))
			? $this->formatArgs(new MacroTokens($s))
			: '"' . $s . '"';
	}



	/**
	 * @return bool
	 */
	public function canQuote(MacroTokens $tokens)
	{
		return $tokens->isCurrent(MacroTokens::T_SYMBOL)
			&& (!$tokens->isPrev() || $tokens->isPrev(',', '(', '[', '=', '=>', ':', '?'))
			&& (!$tokens->isNext() || $tokens->isNext(',', ')', ']', '=', '=>', ':', '|'));
	}



	/**
	 * Preprocessor for tokens. (It advances tokenizer to the end as a side effect.)
	 * @return MacroTokens
	 */
	public function preprocess(MacroTokens $tokens = NULL)
	{
		$tokens = $tokens === NULL ? $this->tokens : $tokens;
		$inTernary = $prev = NULL;
		$res = $arrays = array();
		while ($token = $tokens->nextToken()) {
			$token['depth'] = $depth = count($arrays);

			if ($tokens->isCurrent(MacroTokens::T_COMMENT)) {
				continue; // remove comments

			} elseif ($tokens->isCurrent(MacroTokens::T_WHITESPACE)) {
				$res[] = $token;
				continue;
			}

			if ($tokens->isCurrent('?')) { // short ternary operators without :
				$inTernary = $depth;

			} elseif ($tokens->isCurrent(':')) {
				$inTernary = NULL;

			} elseif ($inTernary === $depth && $tokens->isCurrent(',', ')', ']')) { // close ternary
				$res[] = array(Tokenizer::VALUE => ':', Tokenizer::TYPE => NULL, 'depth' => $depth);
				$res[] = array(Tokenizer::VALUE => 'null', Tokenizer::TYPE => NULL, 'depth' => $depth);
				$inTernary = NULL;
			}

			if ($tokens->isCurrent('[')) { // simplified array syntax [...]
				if ($arrays[] = !$tokens->isPrev(']', ')', MacroTokens::T_SYMBOL, MacroTokens::T_VARIABLE, MacroTokens::T_KEYWORD)) {
					$res[] = array(Tokenizer::VALUE => 'array', Tokenizer::TYPE => NULL, 'depth' => $depth);
					$token = array(Tokenizer::VALUE => '(', Tokenizer::TYPE => NULL, 'depth' => $depth);

				}
			} elseif ($tokens->isCurrent(']')) {
				if (array_pop($arrays) === TRUE) {
					$token = array(Tokenizer::VALUE => ')', Tokenizer::TYPE => NULL, 'depth' => $depth);
				}
			} elseif ($tokens->isCurrent('(')) { // only count
				$arrays[] = '(';

			} elseif ($tokens->isCurrent(')')) { // only count
				array_pop($arrays);
			}

			$res[] = $prev = $token;
		}

		if ($inTernary !== NULL) { // close ternary
			$res[] = array(Tokenizer::VALUE => ':', Tokenizer::TYPE => NULL, 'depth' => count($arrays));
			$res[] = array(Tokenizer::VALUE => 'null', Tokenizer::TYPE => NULL, 'depth' => count($arrays));
		}

		$tokens = clone $tokens;
		$tokens->reset();
		$tokens->tokens = $res;
		return $tokens;
	}



	public function escape($s)
	{
		switch ($this->compiler->getContentType()) {
		case Compiler::CONTENT_XHTML:
		case Compiler::CONTENT_HTML:
			$context = $this->compiler->getContext();
			switch ($context[0]) {
			case Compiler::CONTEXT_SINGLE_QUOTED_ATTR:
			case Compiler::CONTEXT_DOUBLE_QUOTED_ATTR:
			case Compiler::CONTEXT_UNQUOTED_ATTR:
				if ($context[1] === Compiler::CONTENT_JS) {
					$s = "Nette\\Templating\\Helpers::escapeJs($s)";
				} elseif ($context[1] === Compiler::CONTENT_CSS) {
					$s = "Nette\\Templating\\Helpers::escapeCss($s)";
				}
				$quote = $context[0] === Compiler::CONTEXT_SINGLE_QUOTED_ATTR ? ', ENT_QUOTES' : '';
				$s = "htmlSpecialChars($s$quote)";
				return $context[0] === Compiler::CONTEXT_UNQUOTED_ATTR ? "'\"' . $s . '\"'" : $s;
			case Compiler::CONTEXT_COMMENT:
				return "Nette\\Templating\\Helpers::escapeHtmlComment($s)";
			case Compiler::CONTENT_JS:
			case Compiler::CONTENT_CSS:
				return 'Nette\Templating\Helpers::escape' . ucfirst($context[0]) . "($s)";
			default:
				return "Nette\\Templating\\Helpers::escapeHtml($s, ENT_NOQUOTES)";
			}
		case Compiler::CONTENT_XML:
		case Compiler::CONTENT_JS:
		case Compiler::CONTENT_CSS:
		case Compiler::CONTENT_ICAL:
			return 'Nette\Templating\Helpers::escape' . ucfirst($this->compiler->getContentType()) . "($s)";
		case Compiler::CONTENT_TEXT:
			return $s;
		default:
			return "\$template->escape($s)";
		}
	}

}
