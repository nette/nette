<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte\Macros;

use Nette,
	Nette\Latte,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter,
	Nette\Latte\CompileException,
	Nette\Forms\Form,
	Nette\Utils\Strings;



/**
 * Macros for Nette\Forms.
 *
 * - {form name} ... {/form}
 * - {input name}
 * - {label name /} or {label name}... {/label}
 * - {formContainer name} ... {/formContainer}
 *
 * @author     David Grudl
 */
class FormMacros extends MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('form', array($me, 'macroForm'), 'Nette\Latte\Macros\FormMacros::renderFormEnd($_form)');
		$me->addMacro('formContainer', array($me, 'macroFormContainer'), '$_form = array_pop($_formStack)');
		$me->addMacro('label', array($me, 'macroLabel'), array($me, 'macroLabelEnd'));
		$me->addMacro('input', array($me, 'macroInput'), NULL, array($me, 'macroAttrInput'));
	}



	/********************* macros ****************d*g**/



	/**
	 * {form ...}
	 */
	public function macroForm(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write(
			'Nette\Latte\Macros\FormMacros::renderFormBegin($form = $_form = '
			. ($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
			. '$_control[%node.word], %node.array)'
		);
	}



	/**
	 * {formContainer ...}
	 */
	public function macroFormContainer(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write(
			'$_formStack[] = $_form; $formContainer = $_form = ' . ($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '') . '$_form[%node.word]'
		);
	}



	/**
	 * {label ...}
	 */
	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		list($name) = $pair = explode(':', $node->tokenizer->fetchWord(), 2);
		if ($name === '') {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; if ($_label = $_input' : 'if ($_label = $_form[%0.word]')
			. '->getLabel(%1.raw)) echo $_label->addAttributes(%node.array)',
			$name,
			isset($pair[1]) ? 'NULL, ' . $writer->formatWord($pair[1]) : ''
		);
	}



	/**
	 * {/label}
	 */
	public function macroLabelEnd(MacroNode $node, PhpWriter $writer)
	{
		if ($node->content != NULL) {
			$node->openingCode = substr_replace($node->openingCode, '->startTag()', strrpos($node->openingCode, ')') + 1, 0);
			return $writer->write('?></label><?php');
		}
	}



	/**
	 * {input ...}
	 */
	public function macroInput(MacroNode $node, PhpWriter $writer)
	{
		list($name) = $pair = explode(':', $node->tokenizer->fetchWord(), 2);
		if ($name === '') {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; echo $_input' : 'echo $_form[%0.word]')
			. '->getControl(%1.raw)->addAttributes(%node.array)',
			$name,
			isset($pair[1]) ? $writer->formatWord($pair[1]) : ''
		);
	}



	/**
	 * n:input
	 */
	public function macroAttrInput(MacroNode $node, PhpWriter $writer)
	{
		list($name) = $pair = explode(':', $node->tokenizer->fetchWord(), 2);
		if ($name === '') {
			throw new CompileException("Missing name in n:input.");
		}
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; echo $_input' : 'echo $_form[%0.word]')
			. '->getControl(%1.raw)' . ($node->htmlNode->attrs ? '->addAttributes(%2.var)' : '') . '->attributes()',
			$name,
			isset($pair[1]) ? $writer->formatWord($pair[1]) : '',
			array_fill_keys(array_keys($node->htmlNode->attrs), NULL)
		);
	}



	/********************* run-time writers ****************d*g**/



	/**
	 * Renders form begin.
	 * @return void
	 */
	public static function renderFormBegin(Form $form, array $attrs)
	{
		$el = $form->getElementPrototype();
		$el->action = $action = (string) $el->action;
		$el = clone $el;
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			list($el->action) = explode('?', $action, 2);
			if (($i = strpos($action, '#')) !== FALSE) {
				$el->action .= substr($action, $i);
			}
		}
		echo $el->addAttributes($attrs)->startTag();
	}



	/**
	 * Renders form end.
	 * @return string
	 */
	public static function renderFormEnd(Form $form)
	{
		$s = '';
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			$url = explode('?', $form->getElementPrototype()->action, 2);
			if (isset($url[1])) {
				list($url[1]) = explode('#', $url[1], 2);
				foreach (preg_split('#[;&]#', $url[1]) as $param) {
					$parts = explode('=', $param, 2);
					$name = urldecode($parts[0]);
					if (!isset($form[$name])) {
						$s .= Nette\Utils\Html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])));
					}
				}
			}
		}

		foreach ($form->getComponents(TRUE, 'Nette\Forms\Controls\HiddenField') as $control) {
			if (!$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}

		if (iterator_count($form->getComponents(TRUE, 'Nette\Forms\Controls\TextInput')) < 2) {
			$s .= '<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';
		}

		echo ($s ? "<div>$s</div>\n" : '') . $form->getElementPrototype()->endTag() . "\n";
	}

}
