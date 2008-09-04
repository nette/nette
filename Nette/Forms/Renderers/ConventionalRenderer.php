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
		'error' => 'error',
	);


	/** @var Form */
	private $form;

	/** @var  */
	private $js;



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

			$js = $this->getJs();
			if ($js) {
				$s .= $js->renderClientScript() . "\n";
			}
		}
		return $s;
	}



	/**
	 * Sets JavaScript handler.
	 * @param
	 * @return void
	 */
	public function setJs($js = NULL)
	{
		$this->js = $js;
	}



	/**
	 * Returns JavaScript handler.
	 * @return |NULL
	 */
	public function getJs()
	{
		if ($this->js === NULL) {
			$this->js = new InstantClientScript($this->form);
		}
		return $this->js;
	}



	/**
	 * Initializes form.
	 * @return void
	 */
	protected function init()
	{
		$js = $this->getJs();
		if ($js) {
			$js->enable();
		}

		foreach ($this->form->getControls() as $control) {
			$control->setRendered(FALSE);

			if ($control->isRequired()) {
				// TODO: only for back compatiblity - remove?
				$control->getLabelPrototype()->class($this->classes['required']);
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
			$ul->class($this->classes['error']);
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
		$label = Html::el($this->wrappers['control']['label']);
		$ctrl = Html::el($this->wrappers['control']['control']);
		$hidden = Html::el($this->wrappers['hidden']['container']);

		foreach ($parent->getControls() as $control) {
			if ($control->isRendered()) {
				// skip

			} elseif ($control instanceof HiddenField) {
				$hidden->add($control->getControl());

			} else {
				$labelEl = $control->label;
				$controlEl = $control->control;

				$pair->class($control->isRequired() ? $this->classes['required'] : NULL);

				if ($control instanceof Checkbox) {
					$controlEl = (string) $controlEl . (string) $labelEl;
					$labelEl = '&nbsp;';

				} elseif (!$labelEl) {
					$labelEl = '&nbsp;';
				}

				$container->add("\n" . $pair->startTag() . "\n\t"
					. $label->startTag() . $labelEl . $label->endTag() . "\n\t"
					. $ctrl->startTag() . $controlEl . $ctrl->endTag() . "\n"
					. $pair->endTag() . "\n");
			}
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

}
