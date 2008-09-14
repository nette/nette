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
		),
		'control' => array(
			'container' => 'table',
			'pair' => 'tr',
			'control' => 'td',
			'label' => 'th',
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
	private $clientScript;



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
			if ($clientScript) {
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
		if ($this->clientScript === NULL) {
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
		if ($clientScript) {
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
			$ul = Html::el($this->wrappers['error']['container']);
			$ul->class[] = $this->classes['error'];
			foreach ($errors as $error) {
				$ul->create($this->wrappers['error']['item'], $error);
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
		$defaultContainer = Html::el($this->wrappers['group']['container']);
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
				$s .= "\n" . Html::el($this->wrappers['group']['label'])->setText($text) . "\n";
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
	protected function renderControls($parent)
	{
		$container = Html::el($this->wrappers['control']['container']);
		$pair = Html::el($this->wrappers['control']['pair']);
		$label = $pair->create($this->wrappers['control']['label']);
		$ctrl = $pair->create($this->wrappers['control']['control']);
		$hidden = Html::el($this->wrappers['hidden']['container']);

		$s = $buttons = NULL;
		foreach ($parent->getControls() as $control) {
			if ($control->isRendered()) {
				// skip

			} elseif ($control instanceof HiddenField) {
				$hidden->add($control->getControl());

			} elseif ($control instanceof Button) {
				$buttons[] = $control->getControl();

			} else {
				if ($buttons) {
					$label->setHtml('&nbsp;');
					$ctrl->setHtml(implode(" ", $buttons));
					$container->add($pair->render(0));
					$buttons = NULL;
				}

				$labelEl = $control->getLabel();
				$controlEl = $control->getControl();

				$pair->class = $control->isRequired() ? $this->classes['required'] : $this->classes['optional'];

				if ($control instanceof Checkbox) {
					$label->setHtml('&nbsp;');
					$ctrl->setHtml((string) $controlEl . (string) $labelEl);

				} else {
					$label->setHtml((string) $labelEl);
					$ctrl->setHtml((string) $controlEl);
				}

				$container->add($pair->render(0));
			}
		}

		if ($buttons) {
			$label->setHtml('&nbsp;');
			$ctrl->setHtml(implode(" ", $buttons));
			$container->add($pair->render(0));
		}

		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		if (count($hidden)) {
			$s .= "\n" . $hidden . "\n";
		}
		return $s;
	}

}
