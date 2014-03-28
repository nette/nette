<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette,
	Nette\Latte,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter,
	Nette\Latte\CompileException,
	Nette\Utils\Strings;


/**
 * Macros for Nette\Application\UI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {status ...} HTTP status
 *
 * @author     David Grudl
 */
class UIMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('control', array($me, 'macroControl'));

		$me->addMacro('href', NULL, NULL, function(MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), '}'); // deprecated; use n:class="$presenter->linkCurrent ? ..."

		$me->addMacro('status', array($me, 'macroStatus'));
	}


	/********************* macros ****************d*g**/


	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing control name in {control}");
		}
		$name = $writer->formatWord($words[0]);
		$method = isset($words[1]) ? ucfirst($words[1]) : '';
		$method = Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";
		$param = $writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_ctrl = $_control->getComponent(' . $name . '); '
			. 'if ($_ctrl instanceof Nette\Application\UI\IRenderable) $_ctrl->redrawControl(NULL, FALSE); '
			. ($node->modifiers === '' ? "\$_ctrl->$method($param)" : $writer->write("ob_start(); \$_ctrl->$method($param); echo %modify(ob_get_clean())"));
	}


	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(MacroNode $node, PhpWriter $writer)
	{
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
		return $writer->using($node, $this->getCompiler())
			->write('echo %escape(%modify(' . ($node->name === 'plink' ? '$_presenter' : '$_control') . '->link(%node.word, %node.array?)))');
	}


	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write(($node->args ? 'try { $_presenter->link(%node.word, %node.array?); } catch (Nette\Application\UI\InvalidLinkException $e) {}' : '')
			. '; if ($_presenter->getLastCreatedRequestFlag("current")) {');
	}


	/**
	 * {status ...}
	 */
	public function macroStatus(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write((substr($node->args, -1) === '?' ? 'if (!$netteHttpResponse->isSent()) ' : '') .
			'$netteHttpResponse->setCode(%var)', (int) $node->args
		);
	}

}
