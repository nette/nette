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
	/** @var array of HTML tags */
	public $wrappers = array(
		'error' => array(
			'container' => 'ul',
			'item' => 'li',
		),
		'group' => array(
			'container' => 'fieldset',
			'label' => 'legend',
			'description' => 'p',
		),
		'control' => array(
			'container' => 'table',
			'pair' => 'tr',
			'control' => 'td',
			'label' => 'th',
			'description' => 'small',
		),
		'hidden' => array(
			'container' => 'div',
		),
	);

	/** @var array of HTML tags */
	public $classes = array(
		'required' => 'required',
		'optional' => NULL,
		'error' => 'error',
		'text' => 'text',
		'password' => 'text',
		'file' => 'text',
		'submit' => 'button',
		'button' => 'button',
	);


	/** @var Form */
	private $form;

	/** @var  */
	private $clientScript = TRUE; // means autodetect



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
		if (!$mode || $mode === 'errors') {
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

		foreach ($this->form->getControls() as $control) {
			$control->setRendered(FALSE);

			if ($control->isRequired()) {
				// TODO: only for back compatiblity - remove?
				$control->getLabelPrototype()->class[] = $this->classes['required'];
			}

			$el = $control->getControlPrototype();
			if ($el->getName() === 'input' && isset($this->classes[$el->type])) {
				$el->class[] = $this->classes[$el->type];
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
			$ul->class[] = $this->classes['error'];
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
		$defaultContainer = $this->getHtml($this->wrappers['group']['container']);
		$translator = $this->form->getTranslator();

		$s = $remains = '';
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
				$s .= $remains . $container->endTag() . "\n";
				$remains = '';
			}
		}

		$s .= $remains . $this->renderControls($this->form);
		return $s;
	}



	/**
	 * Renders group of controls.
	 * @param  Form|FormGroup
	 * @return string
	 */
	public function renderControls($parent)
	{
		$container = $this->getHtml($this->wrappers['control']['container']);
		$hidden = $this->getHtml($this->wrappers['hidden']['container']);

		$buttons = NULL;
		foreach ($parent->getControls() as $control) {
			if ($control->isRendered()) {
				// skip

			} elseif ($control instanceof HiddenField) {
				$hidden->add($control->getControl());

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

		if (count($hidden)) {
			$s .= "\n" . $hidden . "\n";
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
		$pair = $this->getHtml($this->wrappers['control']['pair']);
		$pair->add($this->renderLabel($control));
		$pair->add($this->renderControl($control));
		$pair->class[] = $control->isRequired() ? $this->classes['required'] : $this->classes['optional'];
		$pair->class[] = $control->getOption('class');
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
		$pair = $this->getHtml($this->wrappers['control']['pair']);
		$pair->add($this->getHtml($this->wrappers['control']['label'])->setHtml('&nbsp;'));
		$pair->add($this->getHtml($this->wrappers['control']['control'])->setHtml(implode(" ", $s)));
		return $pair->render(0);
	}



	/**
	 * Renders 'label' part of visual row of controls.
	 * @param  IFormControl
	 * @return string
	 */
	public function renderLabel(IFormControl $control)
	{
		$head = $this->getHtml($this->wrappers['control']['label']);

		if ($control instanceof Checkbox || $control instanceof Button) {
			return $head->setHtml('&nbsp;');

		} else {
			return $head->setHtml((string) $control->getLabel());
		}
	}



	/**
	 * Renders 'control' part of visual row of controls.
	 * @param  IFormControl
	 * @return string
	 */
	public function renderControl(IFormControl $control)
	{
		$body = $this->getHtml($this->wrappers['control']['control']);

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
