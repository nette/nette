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
		$me->addMacro('input', array($me, 'macroInput'), NULL, array($me, 'macroInputAttr'));
		$me->addMacro('name', array($me, 'macroName'), array($me, 'macroNameEnd'), array($me, 'macroNameAttr'));
	}


	/********************* macros ****************d*g**/


	/**
	 * {form ...}
	 */
	public function macroForm(MacroNode $node, PhpWriter $writer)
	{
		if ($node->htmlNode && strtolower($node->htmlNode->name) === 'form') {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}
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
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; if ($_label = $_input' : 'if ($_label = $_form[%0.word]')
			. '->getLabel(%1.raw)) echo $_label->addAttributes(%node.array)',
			$name,
			($words ? 'NULL, ' : '') . implode(', ', array_map(array($writer, 'formatWord'), $words))
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
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; echo $_input' : 'echo $_form[%0.word]')
			. '->getControl(%1.raw)->addAttributes(%node.array)',
			$name,
			implode(', ', array_map(array($writer, 'formatWord'), $words))
		);
	}


	/**
	 * deprecated n:input
	 */
	public function macroInputAttr(MacroNode $node, PhpWriter $writer)
	{
		if (strtolower($node->htmlNode->name) === 'input') {
			return $this->macroNameAttr($node, $writer);
		} else {
			throw new CompileException("Use n:name instead of n:input.");
		}
	}


	/**
	 * <form n:name>, <input n:name>, <select n:name>, <textarea n:name> and <label n:name>
	 */
	public function macroNameAttr(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in n:{$node->name}.");
		}
		$name = array_shift($words);
		$tagName = strtolower($node->htmlNode->name);
		$node->isEmpty = !in_array($tagName, array('form', 'select', 'textarea'));

		if ($tagName === 'form') {
			return $writer->write(
				'Nette\Latte\Macros\FormMacros::renderFormBegin($form = $_form = '
				. ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
				. '$_control[%0.word], %1.var, FALSE)',
				$name,
				array_fill_keys(array_keys($node->htmlNode->attrs), NULL)
			);
		} else {
			return $writer->write(
				'$_input = ' . ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
				. '$_form[%0.word]; echo $_input'
				. ($tagName === 'label' ? '->getLabel(%1.raw)' : '->getControl(%1.raw)')
				. ($node->htmlNode->attrs ? '->addAttributes(%2.var)' : '') . '->attributes()',
				$name,
				implode(', ', array_map(array($writer, 'formatWord'), $words)),
				array_fill_keys(array_keys($node->htmlNode->attrs), NULL)
			);
		}
	}


	public function macroName(MacroNode $node, PhpWriter $writer)
	{
		if (!$node->htmlNode) {
			throw new CompileException("Unknown macro {{$node->name}}, use n:{$node->name} attribute.");
		} elseif ($node->prefix !== MacroNode::PREFIX_NONE) {
			throw new CompileException("Unknown attribute n:{$node->prefix}-{$node->name}, use n:{$node->name} attribute.");
		}
	}


	public function macroNameEnd(MacroNode $node, PhpWriter $writer)
	{
		preg_match('#(^.*?>)(.*)(<.*\z)#s', $node->content, $parts);
		if (strtolower($node->htmlNode->name) === 'form') {
			$node->content = $parts[1] . $parts[2] . '<?php Nette\Latte\Macros\FormMacros::renderFormEnd($_form, FALSE) ?>' . $parts[3];
		} else { // select, textarea
			$node->content = $parts[1] . '<?php echo $_input->getControl()->getHtml() ?>' . $parts[3];
		}
	}


	/********************* run-time writers ****************d*g**/


	/**
	 * Renders form begin.
	 * @return void
	 */
	public static function renderFormBegin(Form $form, array $attrs, $withTags = TRUE)
	{
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}
		$el = $form->getElementPrototype();
		$el->action = $action = (string) $el->action;
		$el = clone $el;
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			$el->action = preg_replace('~\?[^#]*~', '', $el->action, 1);
		}
		$el->addAttributes($attrs);
		echo $withTags ? $el->startTag() : $el->attributes();
	}


	/**
	 * Renders form end.
	 * @return string
	 */
	public static function renderFormEnd(Form $form, $withTags = TRUE)
	{
		$s = '';
		if (strcasecmp($form->getMethod(), 'get') === 0) {
			foreach (preg_split('#[;&]#', parse_url($form->getElementPrototype()->action, PHP_URL_QUERY), NULL, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				if (!isset($form[$name])) {
					$s .= Nette\Utils\Html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])));
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

		echo ($s ? "<div>$s</div>\n" : '') . ($withTags ? $form->getElementPrototype()->endTag() . "\n" : '');
	}

}
