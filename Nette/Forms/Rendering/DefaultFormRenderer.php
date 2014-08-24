<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Rendering;

use Nette,
	Nette\Utils\Html;


/**
 * Converts a Form into the HTML output.
 *
 * @author     David Grudl
 */
class DefaultFormRenderer extends Nette\Object implements Nette\Forms\IFormRenderer
{
	/**
	 *  /--- form.container
	 *
	 *    /--- error.container
	 *      .... error.item [.class]
	 *    \---
	 *
	 *    /--- hidden.container
	 *      .... HIDDEN CONTROLS
	 *    \---
	 *
	 *    /--- group.container
	 *      .... group.label
	 *      .... group.description
	 *
	 *      /--- controls.container
	 *
	 *        /--- pair.container [.required .optional .odd]
	 *
	 *          /--- label.container
	 *            .... LABEL
	 *            .... label.suffix
	 *            .... label.requiredsuffix
	 *          \---
	 *
	 *          /--- control.container [.odd]
	 *            .... CONTROL [.required .text .password .file .submit .button]
	 *            .... control.requiredsuffix
	 *            .... control.description
	 *            .... control.errorcontainer + control.erroritem
	 *          \---
	 *        \---
	 *      \---
	 *    \---
	 *  \--
	 *
	 * @var array of HTML tags */
	public $wrappers = array(
		'form' => array(
			'container' => NULL,
		),

		'error' => array(
			'container' => 'ul class=error',
			'item' => 'li',
		),

		'group' => array(
			'container' => 'fieldset',
			'label' => 'legend',
			'description' => 'p',
		),

		'controls' => array(
			'container' => 'table',
		),

		'pair' => array(
			'container' => 'tr',
			'.required' => 'required',
			'.optional' => NULL,
			'.odd' => NULL,
			'.error' => NULL,
		),

		'control' => array(
			'container' => 'td',
			'.odd' => NULL,

			'description' => 'small',
			'requiredsuffix' => '',
			'errorcontainer' => 'span class=error',
			'erroritem' => '',

			'.required' => 'required',
			'.text' => 'text',
			'.password' => 'text',
			'.file' => 'text',
			'.submit' => 'button',
			'.image' => 'imagebutton',
			'.button' => 'button',
		),

		'label' => array(
			'container' => 'th',
			'suffix' => NULL,
			'requiredsuffix' => '',
		),

		'hidden' => array(
			'container' => 'div',
		),
	);

	/** @var Nette\Forms\Form */
	protected $form;

	/** @var int */
	protected $counter;


	/**
	 * Provides complete form rendering.
	 * @param  Nette\Forms\Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $this->renderBegin();
		}
		if (!$mode || strtolower($mode) === 'ownerrors') {
			$s .= $this->renderErrors();

		} elseif ($mode === 'errors') {
			$s .= $this->renderErrors(NULL, FALSE);
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if (!$mode || $mode === 'end') {
			$s .= $this->renderEnd();
		}
		return $s;
	}


	/**
	 * Initializes form.
	 * @return void
	 */
	protected function init()
	{
		$wrapper = & $this->wrappers['control'];
		foreach ($this->form->getControls() as $control) {
			if ($control->isRequired() && isset($wrapper['.required'])) {
				$control->getLabelPrototype()->class($wrapper['.required'], TRUE);
			}

			$el = $control->getControlPrototype();
			if ($el->getName() === 'input' && isset($wrapper['.' . $el->type])) {
				$el->class($wrapper['.' . $el->type], TRUE);
			}
		}
	}


	/**
	 * Renders form begin.
	 * @return string
	 */
	public function renderBegin()
	{
		$this->counter = 0;

		foreach ($this->form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}

		if (strcasecmp($this->form->getMethod(), 'get') === 0) {
			$el = clone $this->form->getElementPrototype();
			$query = parse_url($el->action, PHP_URL_QUERY);
			$el->action = str_replace("?$query", '', $el->action);
			$s = '';
			foreach (preg_split('#[;&]#', $query, NULL, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				if (!isset($this->form[$name])) {
					$s .= Html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])));
				}
			}
			return $el->startTag() . ($s ? "\n\t" . $this->getWrapper('hidden container')->setHtml($s) : '');

		} else {
			return $this->form->getElementPrototype()->startTag();
		}
	}


	/**
	 * Renders form end.
	 * @return string
	 */
	public function renderEnd()
	{
		$s = '';
		foreach ($this->form->getControls() as $control) {
			if ($control instanceof Nette\Forms\Controls\HiddenField && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}
		if (iterator_count($this->form->getComponents(TRUE, 'Nette\Forms\Controls\TextInput')) < 2) {
			$s .= '<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';
		}
		if ($s) {
			$s = $this->getWrapper('hidden container')->setHtml($s) . "\n";
		}

		return $s . $this->form->getElementPrototype()->endTag() . "\n";
	}


	/**
	 * Renders validation errors (per form or per control).
	 * @return string
	 */
	public function renderErrors(Nette\Forms\IControl $control = NULL, $own = TRUE)
	{
		$errors = $control
			? $control->getErrors()
			: ($own ? $this->form->getOwnErrors() : $this->form->getErrors());
		if (!$errors) {
			return;
		}
		$container = $this->getWrapper($control ? 'control errorcontainer' : 'error container');
		$item = $this->getWrapper($control ? 'control erroritem' : 'error item');

		foreach ($errors as $error) {
			$item = clone $item;
			if ($error instanceof Html) {
				$item->add($error);
			} else {
				$item->setText($error);
			}
			$container->add($item);
		}
		return "\n" . $container->render($control ? 1 : 0);
	}


	/**
	 * Renders form body.
	 * @return string
	 */
	public function renderBody()
	{
		$s = $remains = '';

		$defaultContainer = $this->getWrapper('group container');
		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if (!$group->getControls() || !$group->getOption('visual')) {
				continue;
			}

			$container = $group->getOption('container', $defaultContainer);
			$container = $container instanceof Html ? clone $container : Html::el($container);

			$s .= "\n" . $container->startTag();

			$text = $group->getOption('label');
			if ($text instanceof Html) {
				$s .= $this->getWrapper('group label')->add($text);

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
			}

			$text = $group->getOption('description');
			if ($text instanceof Html) {
				$s .= $text;

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= $this->getWrapper('group description')->setText($text) . "\n";
			}

			$s .= $this->renderControls($group);

			$remains = $container->endTag() . "\n" . $remains;
			if (!$group->getOption('embedNext')) {
				$s .= $remains;
				$remains = '';
			}
		}

		$s .= $remains . $this->renderControls($this->form);

		$container = $this->getWrapper('form container');
		$container->setHtml($s);
		return $container->render(0);
	}


	/**
	 * Renders group of controls.
	 * @param  Nette\Forms\Container|FormGroup
	 * @return string
	 */
	public function renderControls($parent)
	{
		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException('Argument must be FormContainer or FormGroup instance.');
		}

		$container = $this->getWrapper('controls container');

		$buttons = NULL;
		foreach ($parent->getControls() as $control) {
			if ($control->getOption('rendered') || $control instanceof Nette\Forms\Controls\HiddenField || $control->getForm(FALSE) !== $this->form) {
				// skip

			} elseif ($control instanceof Nette\Forms\Controls\Button) {
				$buttons[] = $control;

			} else {
				if ($buttons) {
					$container->add($this->renderPairMulti($buttons));
					$buttons = NULL;
				}
				$container->add($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->add($this->renderPairMulti($buttons));
		}

		$s = '';
		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		return $s;
	}


	/**
	 * Renders single visual row.
	 * @return string
	 */
	public function renderPair(Nette\Forms\IControl $control)
	{
		$pair = $this->getWrapper('pair container');
		$pair->add($this->renderLabel($control));
		$pair->add($this->renderControl($control));
		$pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
		$pair->class($control->hasErrors() ? $this->getValue('pair .error') : NULL, TRUE);
		$pair->class($control->getOption('class'), TRUE);
		if (++$this->counter % 2) {
			$pair->class($this->getValue('pair .odd'), TRUE);
		}
		$pair->id = $control->getOption('id');
		return $pair->render(0);
	}


	/**
	 * Renders single visual row of multiple controls.
	 * @param  IFormControl[]
	 * @return string
	 */
	public function renderPairMulti(array $controls)
	{
		$s = array();
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\IControl) {
				throw new Nette\InvalidArgumentException('Argument must be array of IFormControl instances.');
			}
			$description = $control->getOption('description');
			if ($description instanceof Html) {
				$description = ' ' . $control->getOption('description');

			} elseif (is_string($description)) {
				$description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));

			} else {
				$description = '';
			}

			$s[] = $control->getControl() . $description;
		}
		$pair = $this->getWrapper('pair container');
		$pair->add($this->renderLabel($control));
		$pair->add($this->getWrapper('control container')->setHtml(implode(' ', $s)));
		return $pair->render(0);
	}


	/**
	 * Renders 'label' part of visual row of controls.
	 * @return string
	 */
	public function renderLabel(Nette\Forms\IControl $control)
	{
		$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
		$label = $control->getLabel();
		if ($label instanceof Html) {
			$label->add($suffix);
		} elseif ($label != NULL) { // @intentionally ==
			$label .= $suffix;
		}
		return $this->getWrapper('label container')->setHtml($label);
	}


	/**
	 * Renders 'control' part of visual row of controls.
	 * @return string
	 */
	public function renderControl(Nette\Forms\IControl $control)
	{
		$body = $this->getWrapper('control container');
		if ($this->counter % 2) {
			$body->class($this->getValue('control .odd'), TRUE);
		}

		$description = $control->getOption('description');
		if ($description instanceof Html) {
			$description = ' ' . $description;

		} elseif (is_string($description)) {
			$description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));

		} else {
			$description = '';
		}

		if ($control->isRequired()) {
			$description = $this->getValue('control requiredsuffix') . $description;
		}

		$el = $control->getControl();
		return $body->setHtml($el . $description . $this->renderErrors($control));
	}


	/**
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	protected function getWrapper($name)
	{
		$data = $this->getValue($name);
		return $data instanceof Html ? clone $data : Html::el($data);
	}


	/**
	 * @param  string
	 * @return string
	 */
	protected function getValue($name)
	{
		$name = explode(' ', $name);
		$data = & $this->wrappers[$name[0]][$name[1]];
		return $data;
	}

}
