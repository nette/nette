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
	Nette\Latte\ParseException,
	Nette\Utils\Strings;



/**
 * Macros for Nette\Forms.
 *
 * - {form name} ... {/form}
 * - {input name}
 * - {label name /} or {label name}... {/label}
 * - {inputlist name} ... {/inputlist}
 *
 * @author     David Grudl
 */
class FormMacros extends MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('form',
			'Nette\Latte\Macros\FormMacros::renderFormBegin($form = $_control[%node.word], %node.array)',
			'Nette\Latte\Macros\FormMacros::renderFormEnd($form)');
		$me->addMacro('label', array($me, 'macroLabel'), '?></label><?php');
		$me->addMacro('input', array($me, 'macroInput'));
		$me->addMacro('inputlist',
			'$_inputlist = $form[%node.word]; $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($_inputlist->getItems()) as $_inputlistKey => $_inputlistItem):',
			'$iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its); unset($_inputlist)');
	}



	/********************* macros ****************d*g**/


	/**
	 * {input}
	 */
	public function macroInput(MacroNode $node, $writer)
	{
		while ($node->parentNode) {
			if ($node->parentNode->name == 'inputlist') {
				return $writer->write('echo $_inputlist->getItemControl($_inputlistKey)->addAttributes(%node.array)');
			}
			$node = $node->parentNode;
		}
		return $writer->write('echo $form[%node.word]->getControl()->addAttributes(%node.array)');
	}



	/**
	 * {label ...} and optionally {/label}
	 */
	public function macroLabel(MacroNode $node, $writer)
	{
		$cmd = 'if ($_label = $form[%node.word]->getLabel()) echo $_label->addAttributes(%node.array)';
		$parentNode = $node->parentNode;
		while ($parentNode) {
			if ($parentNode->name == 'inputlist') {
				$cmd = 'if ($_label = $_inputlist->getItemLabel($_inputlistKey)) echo $_label->addAttributes(%node.array)';
				break;
			}
			$parentNode = $parentNode->parentNode;
		}
		if ($node->isEmpty = (substr($node->args, -1) === '/')) {
			$node->setArgs(substr($node->args, 0, -1));
			return $writer->write($cmd);
		} else {
			return $writer->write($cmd . '->startTag()');
		}
	}



	/********************* run-time writers ****************d*g**/



	/**
	 * Renders form begin.
	 * @return void
	 */
	public static function renderFormBegin($form, $attrs)
	{
		$el = $form->getElementPrototype();
		$el->action = (string) $el->action;
		$el = clone $el;
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			list($el->action) = explode('?', $el->action, 2);
		}
		echo $el->addAttributes($attrs)->startTag();
	}



	/**
	 * Renders form end.
	 * @return string
	 */
	public static function renderFormEnd($form)
	{
		$s = '';
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			$url = explode('?', $form->getElementPrototype()->action, 2);
			if (isset($url[1])) {
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
