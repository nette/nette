<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte\Macros;

use Nette,
	Nette\Latte,
	Nette\Latte\ParseException,
	Nette\Latte\MacroNode;



/**
 * Basic macros for Latte.
 *
 * - {if ?} ... {elseif ?} ... {else} ... {/if}
 * - {ifset ?} ... {elseifset ?} ... {/ifset}
 * - {for ?} ... {/for}
 * - {foreach ?} ... {/foreach}
 * - {$variable} with escaping
 * - {!$variable} without escaping
 * - {=expression} echo with escaping
 * - {!=expression} echo without escaping
 * - {?expression} evaluate PHP statement
 * - {_expression} echo translation with escaping
 * - {!_expression} echo translation without escaping
 * - {attr ?} HTML element attributes
 * - {capture ?} ... {/capture} capture block to parameter
 * - {var var => value} set template parameter
 * - {default var => value} set default template parameter
 * - {dump $var}
 * - {debugbreak}
 * - {l} {r} to display { }
 *
 * @author     David Grudl
 */
class CoreMacros extends MacroSet
{


	public static function install(Latte\Parser $parser)
	{
		$me = new static($parser);

		$me->addMacro('if', array($me, 'macroIf'), array($me, 'macroEndIf'));
		$me->addMacro('elseif', 'elseif (%node.args):');
		$me->addMacro('else', 'else:');
		$me->addMacro('ifset', 'if (isset(%node.args)):', 'endif');
		$me->addMacro('elseifset', 'elseif (isset(%node.args)):');

		$me->addMacro('foreach', array($me, 'macroForeach'), '$iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its)');
		$me->addMacro('for', 'for (%node.args):', 'endfor');
		$me->addMacro('while', 'while (%node.args):', 'endwhile');
		$me->addMacro('continueIf', 'if (%node.args) continue');
		$me->addMacro('breakIf', 'if (%node.args) break');
		$me->addMacro('first', 'if ($iterator->isFirst(%node.args)):', 'endif');
		$me->addMacro('last', 'if ($iterator->isLast(%node.args)):', 'endif');
		$me->addMacro('sep', 'if (!$iterator->isLast(%node.args)):', 'endif');

		$me->addMacro('var', array($me, 'macroVar'));
		$me->addMacro('assign', array($me, 'macroVar')); // deprecated
		$me->addMacro('default', array($me, 'macroVar'));
		$me->addMacro('dump', array($me, 'macroDump'));
		$me->addMacro('debugbreak', array($me, 'macroDebugbreak'));
		$me->addMacro('l', '?>{<?php');
		$me->addMacro('r', '?>}<?php');

		$me->addMacro('_', array($me, 'macroTranslate'));
		$me->addMacro('=', array($me, 'macroExpr'));
		$me->addMacro('?', array($me, 'macroExpr'));

		$me->addMacro('syntax', array($me, 'macroSyntax'), array($me, 'macroSyntax'));
		$me->addMacro('capture', array($me, 'macroCapture'), array($me, 'macroCaptureEnd'));
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('use', array($me, 'macroUse'));

		$me->addMacro('@href', NULL, NULL); // TODO: placeholder
		$me->addMacro('@class', array($me, 'macroClass'));
		$me->addMacro('@attr', array($me, 'macroAttr'));
		$me->addMacro('attr', array($me, 'macroOldAttr'));
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		return array('list($_l, $_g) = Nette\Latte\Macros\CoreMacros::initRuntime($template, '
			. var_export($this->parser->templateId, TRUE) . ')');
	}



	/********************* macros ****************d*g**/



	/**
	 * {if ...}
	 */
	public function macroIf(MacroNode $node, $writer)
	{
		if ($node->data->capture = ($node->args === '')) {
			return 'ob_start()';
		}
		return $writer->write('if (%node.args):');
	}



	/**
	 * {/if ...}
	 */
	public function macroEndIf(MacroNode $node, $writer)
	{
		if ($node->data->capture) {
			if ($node->args === '') {
				throw new ParseException('Missing condition in {if} macro.');
			}
			return $writer->write('if (%node.args) ob_end_flush(); else ob_end_clean()');
		}
		return 'endif';
	}



	/**
	 * {_$var |modifiers}
	 */
	public function macroTranslate(MacroNode $node, $writer)
	{
		return $writer->write('echo %modify', '$template->translate(' . $writer->formatArgs() . ')');
	}



	/**
	 * {syntax name}
	 */
	public function macroSyntax(MacroNode $node)
	{
		if ($node->closing) {
			$node->args = 'latte';
		}
		switch ($node->args) {
		case '':
		case 'latte':
			$this->parser->setDelimiters('\\{(?![\\s\'"{}])', '\\}'); // {...}
			break;

		case 'double':
			$this->parser->setDelimiters('\\{\\{(?![\\s\'"{}])', '\\}\\}'); // {{...}}
			break;

		case 'asp':
			$this->parser->setDelimiters('<%\s*', '\s*%>'); /* <%...%> */
			break;

		case 'python':
			$this->parser->setDelimiters('\\{[{%]\s*', '\s*[%}]\\}'); // {% ... %} | {{ ... }}
			break;

		case 'off':
			$this->parser->setDelimiters('[^\x00-\xFF]', '');
			break;

		default:
			throw new ParseException("Unknown syntax '$node->args'");
		}
	}



	/**
	 * {include "file" [,] [params]}
	 */
	public function macroInclude(MacroNode $node, $writer)
	{
		$code = $writer->write('Nette\Latte\Macros\CoreMacros::includeTemplate(%node.word, %node.array? + $template->getParams(), $_l->templates[%var])',
			$this->parser->templateId);

		if ($node->modifiers) {
			return $writer->write('echo %modify', $code . '->__toString(TRUE)');
		} else {
			return $code . '->render()';
		}
	}



	/**
	 * {use class MacroSet}
	 */
	public function macroUse(MacroNode $node, $writer)
	{
		call_user_func(array($node->tokenizer->fetchWord(), 'install'), $this->parser)
			->initialize();
	}



	/**
	 * {capture $variable}
	 */
	public function macroCapture(MacroNode $node, $writer)
	{
		$variable = $node->tokenizer->fetchWord();
		if (substr($variable, 0, 1) !== '$') {
			throw new ParseException("Invalid capture block variable '$variable'");
		}
		$node->data->variable = $variable;
		return 'ob_start()';
	}



	/**
	 * {/capture}
	 */
	public function macroCaptureEnd(MacroNode $node, $writer)
	{
		return $writer->write("{$node->data->variable} = %modify", 'ob_get_clean()');
	}



	/**
	 * {foreach ...}
	 */
	public function macroForeach(MacroNode $node, $writer)
	{
		return '$iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator('
			. preg_replace('#(.*)\s+as\s+#i', '$1) as ', $writer->formatArgs(), 1) . '):';
	}



	/**
	 * n:class="..."
	 */
	public function macroClass(MacroNode $node, $writer)
	{
		return $writer->write('if ($_l->tmp = trim(implode(" ", array_unique(%node.array)))) echo \' class="\' . %escape($_l->tmp) . \'"\'');
	}



	/**
	 * n:attr="..."
	 */
	public function macroAttr(MacroNode $node, $writer)
	{
		return $writer->write('if (($_l->tmp = (string) (%node.args)) !== \'\') echo \' @@="\' . %escape($_l->tmp) . \'"\'');
	}



	/**
	 * {attr ...}
	 * @deprecated
	 */
	public function macroOldAttr(MacroNode $node)
	{
		return Nette\Utils\Strings::replace($node->args . ' ', '#\)\s+#', ')->');
	}



	/**
	 * {dump ...}
	 */
	public function macroDump(MacroNode $node, $writer)
	{
		$args = $writer->formatArgs();
		return $writer->write('Nette\Diagnostics\Debugger::barDump(' . ($node->args ? "array(%var => $args)" : 'get_defined_vars()')
			. ', "Template " . str_replace(dirname(dirname($template->getFile())), "\xE2\x80\xA6", $template->getFile()))', $args);
	}



	/**
	 * {debugbreak}
	 */
	public function macroDebugbreak()
	{
		return 'if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break()';
	}



	/**
	 * {var ...}
	 * {default ...}
	 */
	public function macroVar(MacroNode $node, $writer)
	{
		$out = '';
		$var = TRUE;
		$tokenizer = $writer->preprocess();
		while ($token = $tokenizer->fetchToken()) {
			if ($var && ($token['type'] === Latte\MacroTokenizer::T_SYMBOL || $token['type'] === Latte\MacroTokenizer::T_VARIABLE)) {
				if ($node->name === 'default') {
					$out .= "'" . ltrim($token['value'], "$") . "'";
				} else {
					$out .= '$' . ltrim($token['value'], "$");
				}
				$var = NULL;

			} elseif (($token['value'] === '=' || $token['value'] === '=>') && $token['depth'] === 0) {
				$out .= $node->name === 'default' ? '=>' : '=';
				$var = FALSE;

			} elseif ($token['value'] === ',' && $token['depth'] === 0) {
				$out .= $node->name === 'default' ? ',' : ';';
				$var = TRUE;

			} elseif ($var === NULL && $node->name === 'default' && $token['type'] !== Latte\MacroTokenizer::T_WHITESPACE) {
				throw new ParseException("Unexpected '$token[value]' in {default $node->args}");

			} else {
				$out .= $writer->canQuote($tokenizer) ? "'$token[value]'" : $token['value'];
			}
		}
		return $node->name === 'default' ? "extract(array($out), EXTR_SKIP)" : $out;
	}



	/**
	 * {= ...}
	 * {? ...}
	 */
	public function macroExpr(MacroNode $node, $writer)
	{
		return $writer->write(($node->name === '?' ? '' : 'echo ') . '%modify', $writer->formatArgs());
	}



	/********************* run-time helpers ****************d*g**/



	/**
	 * Includes subtemplate.
	 * @param  mixed      included file name or template
	 * @param  array      parameters
	 * @param  Nette\Templating\ITemplate  current template
	 * @return Nette\Templating\Template
	 */
	public static function includeTemplate($destination, $params, $template)
	{
		if ($destination instanceof Nette\Templating\ITemplate) {
			$tpl = $destination;

		} elseif ($destination == NULL) { // intentionally ==
			throw new Nette\InvalidArgumentException("Template file name was not specified.");

		} else {
			$tpl = clone $template;
			if ($template instanceof Nette\Templating\IFileTemplate) {
				if (substr($destination, 0, 1) !== '/' && substr($destination, 1, 1) !== ':') {
					$destination = dirname($template->getFile()) . '/' . $destination;
				}
				$tpl->setFile($destination);
			}
		}

		$tpl->setParams($params); // interface?
		return $tpl;
	}



	/**
	 * Initializes local & global storage in template.
	 * @param  Nette\Templating\ITemplate
	 * @param  string
	 * @return stdClass
	 */
	public static function initRuntime($template, $templateId)
	{
		// local storage
		if (isset($template->_l)) {
			$local = $template->_l;
			unset($template->_l);
		} else {
			$local = (object) NULL;
		}
		$local->templates[$templateId] = $template;

		// global storage
		if (!isset($template->_g)) {
			$template->_g = (object) NULL;
		}

		return array($local, $template->_g);
	}

}
