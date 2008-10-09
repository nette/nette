<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Forms
 * @version    $Id$
 */

/*namespace Nette::Forms;*/

/*use Nette::Web::Html;*/



require_once dirname(__FILE__) . '/../../Object.php';

require_once dirname(__FILE__) . '/../../Forms/IFormRenderer.php';



/**
 * Converts a Form into the HTML output.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class ConventionalRenderer extends /*Nette::*/Object implements IFormRenderer
{
	/**
	 *  /--- form.container
	 *
	 *    /--- if (form.errors) error.container
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
	 *          \---
	 *
	 *          /--- control.container [.odd]
	 *            .... CONTROL [.required .text .password .file .submit .button]
	 *            .... control.description
	 *            .... if (control.errors) error.container
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
			'errors' => TRUE,
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
		),

		'control' => array(
			'container' => 'td',
			'.odd' => NULL,

			'errors' => FALSE,
			'description' => 'small',

			'.required' => 'required',
			'.text' => 'text',
			'.password' => 'text',
			'.file' => 'text',
			'.submit' => 'button',
			'.button' => 'button',
		),

		'label' => array(
			'container' => 'th',
			'suffix' => NULL,
		),

		'hidden' => array(
			'container' => 'div',
		),
	);

	/** @var Form */
	private $form;

	/** @var object */
	private $clientScript = TRUE; // means autodetect

	/** @var int */
	private $counter;



	/**
	 * Provides complete form rendering.
	 * @param  Form
	 * @param  string
	 * @return string
	 */
	public function render(Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $form->getElementPrototype()->startTag();
		}
		if ((!$mode && $this->wrappers['form']['errors']) || $mode === 'errors') {
			$s .= $this->renderErrors();
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if (!$mode || $mode === 'end') {
			$s .= $form->getElementPrototype()->endTag() . "\n";

			$clientScript = $this->getClientScript();
			if ($clientScript !== NULL) {
				$s .= $clientScript->renderClientScript() . "\n";
			}
		}
		return $s;
	}



	/**
	 * Sets JavaScript handler.
	 * @param
	 * @return void
	 */
	public function setClientScript($clientScript = NULL)
	{
		$this->clientScript = $clientScript;
	}



	/**
	 * Returns JavaScript handler.
	 * @return |NULL
	 */
	public function getClientScript()
	{
		if ($this->clientScript === TRUE) {
			$this->clientScript = new InstantClientScript($this->form);
		}
		return $this->clientScript;
	}



	/**
	 * Initializes form.
	 * @return void
	 */
	protected function init()
	{
		$clientScript = $this->getClientScript();
		if ($clientScript !== NULL) {
			$clientScript->enable();
		}

		// TODO: only for back compatiblity - remove?
		foreach ($this->form->getControls() as $control) {
			if ($control->isRequired()) {
				$control->getLabelPrototype()->class[] = $this->wrappers['control']['.required'];
			}

			$el = $control->getControlPrototype();
			if ($el->getName() === 'input' && isset($this->wrappers['control']['.' . $el->type])) {
				$el->class[] = $this->wrappers['control']['.' . $el->type];
			}
		}
	}



	/**
	 * Renders validation errors (per form or per control).
	 * @param  IFormControl
	 * @return void
	 */
	public function renderErrors(IFormControl $control = NULL)
	{
		$errors = $control === NULL ? $this->form->getErrors() : $control->getErrors();
		if (count($errors)) {
			$ul = $this->getHtml($this->wrappers['error']['container']);
			$li = $this->getHtml($this->wrappers['error']['item']);

			foreach ($errors as $error) {
				$item = clone $li;
				if ($error instanceof Html) {
					$item->add($error);
				} else {
					$item->setText($error);
				}
				$ul->add($item);
			}
			return "\n" . $ul->render(0);
		}
	}



	/**
	 * Renders form body.
	 * @return string
	 */
	public function renderBody()
	{
		$this->counter = 0;
		$s = $remains = '';

		foreach ($this->form->getControls() as $control) {
			$control->setRendered(FALSE);

			if ($control instanceof HiddenField) {
				$s .= (string) $control->getControl();
			}
		}

		if ($s) {
			$s = "\n" . $this->getHtml($this->wrappers['hidden']['container'])->setHtml($s) . "\n";
		}

		$defaultContainer = $this->getHtml($this->wrappers['group']['container']);
		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if (!$group->getControls()) continue;

			$container = $group->getOption('container', $defaultContainer);
			$s .= "\n" . $container->startTag();

			$text = $group->getOption('label');
			if ($text != NULL) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= "\n" . $this->getHtml($this->wrappers['group']['label'])->setText($text) . "\n";
			}

			$text = $group->getOption('description');
			if ($text instanceof Html) {
				$s .= $text;

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= $this->getHtml($this->wrappers['group']['description'])->setText($text);
			}

			$s .= $this->renderControls($group);

			if ($group->getOption('embedNext')) {
				$remains .= $container->endTag() . "\n";

			} else {
				$s .= $container->endTag() . $remains . "\n";
				$remains = '';
			}
		}

		$s .= $remains . $this->renderControls($this->form);

		$container = $this->getHtml($this->wrappers['form']['container']);
		$container->setHtml($s);
		return $container->render(0);
	}



	/**
	 * Renders group of controls.
	 * @param  Form|FormGroup
	 * @return string
	 */
	public function renderControls($parent)
	{
		$container = $this->getHtml($this->wrappers['controls']['container']);

		$buttons = NULL;
		foreach ($parent->getControls() as $control) {
			if ($control->isRendered()) {
				// skip

			} elseif ($control instanceof Button) {
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
	 * @param  IFormControl
	 * @return string
	 */
	public function renderPair(IFormControl $control)
	{
		$pair = $this->getHtml($this->wrappers['pair']['container']);
		$pair->add($this->renderLabel($control));
		$pair->add($this->renderControl($control));
		$pair->class[] = $control->isRequired() ? $this->wrappers['pair']['.required'] : $this->wrappers['pair']['.optional'];
		$pair->class[] = $control->getOption('class');
		if (++$this->counter % 2) $pair->class[] = $this->wrappers['pair']['.odd'];
		$pair->id = $control->getOption('id');
		return $pair->render(0);
	}



	/**
	 * Renders single visual row of multiple controls.
	 * @param  array of IFormControl
	 * @return string
	 */
	public function renderPairMulti(array $controls)
	{
		$s = array();
		foreach ($controls as $control) {
			$s[] = (string) $control->getControl();
		}
		$pair = $this->getHtml($this->wrappers['pair']['container']);
		$pair->add($this->getHtml($this->wrappers['label']['container'])->setHtml('&nbsp;'));
		$pair->add($this->getHtml($this->wrappers['control']['container'])->setHtml(implode(" ", $s)));
		return $pair->render(0);
	}



	/**
	 * Renders 'label' part of visual row of controls.
	 * @param  IFormControl
	 * @return string
	 */
	public function renderLabel(IFormControl $control)
	{
		$head = $this->getHtml($this->wrappers['label']['container']);

		if ($control instanceof Checkbox || $control instanceof Button) {
			return $head->setHtml('&nbsp;');

		} else {
			return $head->setHtml((string) $control->getLabel() . $this->wrappers['label']['suffix']);
		}
	}



	/**
	 * Renders 'control' part of visual row of controls.
	 * @param  IFormControl
	 * @return string
	 */
	public function renderControl(IFormControl $control)
	{
		$body = $this->getHtml($this->wrappers['control']['container']);
		if ($this->counter % 2) $body->class[] = $this->wrappers['control']['.odd'];

		$description = $control->getOption('description');
		if ($description instanceof Html) {
			$description = ' ' . $control->getOption('description');

		} elseif (is_string($description)) {
			if ($control->getTranslator() !== NULL) {
				$description = $control->getTranslator()->translate($description);
			}
			$description = ' ' . $this->getHtml($this->wrappers['control']['description'])->setText($description);

		} else {
			$description = '';
		}

		if ($this->wrappers['control']['errors']) {
			$description .= $this->renderErrors($control);
		}

		if ($control instanceof Checkbox || $control instanceof Button) {
			return $body->setHtml((string) $control->getControl() . (string) $control->getLabel() . $description);

		} else {
			return $body->setHtml((string) $control->getControl() . $description);
		}
	}



	/**
	 * @param  string|Nette::Web::Html
	 * @return Nette::Web::Html
	 */
	protected function getHtml(& $data)
	{
		if ($data instanceof Html) {
			return clone $data;

		} else {
			return Html::el($data);
		}
	}

}
