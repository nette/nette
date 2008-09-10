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



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * User validation JavaScript generator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class UserClientScript extends /*Nette::*/Object
{
	/** @var Form */
	private $form;



	public function __construct(Form $form)
	{
		$this->form = $form;
	}



	/**
	 * Generates the client side validation script.
	 * @return void
	 */
	public function renderClientScript()
	{
		$this->form->getElementPrototype()->attrs['data-nette-rules'] = json_encode($this->exportContainer($this->form));
	}



	/**
	 * Exports description for JavaScript.
	 * @return array
	 */
	public function exportContainer(FormContainer $container)
	{
		$data = array();
		foreach ($container->getComponents() as $name => $control) {
			if ($control instanceof FormContainer) {
				$data[$name] = $this->exportContainer($control);

			} elseif ($control instanceof IFormControl) {
				$data[$name] = $this->exportControl($control);
			}
		}
		return array(
			'class' => $container->getClass(),
			'controls' => $data,
		);
	}



	/**
	 * Exports description for JavaScript.
	 * @return array
	 */
	private function exportControl(IFormControl $control)
	{
		return $control->isDisabled() ? NULL : array(
			'class' => $control->getClass(),
			'rules' => $this->exportRules($control->getRules()),
			'opt' => $control instanceof FormControl ? $control->getOptions() : NULL
		);
	}



	/**
	 * Exports rules for JavaScript.
	 * @return array
	 */
	private function exportRules(Rules $rules)
	{
		$data = array();
		foreach ($rules as $rule) {
			if (!is_string($rule->operation)) continue;
			$data[] = array(
				'op' => $rule->operation,
				'neg' => $rule->isNegative,
				'cond' => $rule->isCondition,
				'msg' => $rule->message,
				'id' => $rule->control->getHtmlId(),
				'arg' => $rule->arg instanceof FormControl ? $rule->arg->getHtmlId() : $rule->arg,
				'sub' => $rule->subRules ? $this->exportRules($rule->subRules) : NULL,
			);
		}
		return $data;
		/*return array(
			'rules' => $data,
			'toggles' => $this->toggles,
		);*/
	}

}
