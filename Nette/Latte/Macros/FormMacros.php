<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte\Macros;

use Nette,
	Nette\Latte,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter,
	Nette\Latte\CompileException,
	Nette\Forms\Form;


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
		$me->addMacro('form',
			'Nette\Latte\Macros\FormMacros::renderFormBegin($form = $_form = (is_object(%node.word) ? %node.word : $_control[%node.word]), %node.array)',
			'Nette\Latte\Macros\FormMacros::renderFormEnd($_form)');
		$me->addMacro('label', array($me, 'macroLabel'), 'if ($_label) echo $_label->endTag()');
		$me->addMacro('input', '$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->addAttributes(%node.array)', NULL, array($me, 'macroAttrName'));
		$me->addMacro('formContainer', '$_formStack[] = $_form; $formContainer = $_form = (is_object(%node.word) ? %node.word : $_form[%node.word])', '$_form = array_pop($_formStack)');
		$me->addMacro('name', NULL, NULL, array($me, 'macroAttrName'));
	}


	/********************* macros ****************d*g**/


	/**
	 * {label ...} and optionally {/label}
	 */
	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		$cmd = '$_input = is_object(%node.word) ? %node.word : $_form[%node.word]; if ($_label = $_input->getLabel()) echo $_label->addAttributes(%node.array)';
		if ($node->isEmpty = (substr($node->args, -1) === '/')) {
			$node->setArgs(substr($node->args, 0, -1));
			return $writer->write($cmd);
		} else {
			return $writer->write($cmd . '->startTag()');
		}
	}


	/**
	 * <input n:name> or alias n:input
	 */
	public function macroAttrName(MacroNode $node, PhpWriter $writer)
	{
		if ($node->htmlNode->attrs) {
			$reset = array_fill_keys(array_keys($node->htmlNode->attrs), NULL);
			return $writer->write('$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->addAttributes(%var)->attributes()', $reset);
		}
		return $writer->write('$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->attributes()');
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
