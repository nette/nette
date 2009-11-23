<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Forms
 */

/*namespace Nette\Forms;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Instant validation JavaScript generator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Forms
 */
final class InstantClientScript extends /*Nette\*/Object
{
	/** @var string  JavaScript event handler name */
	public $validateFunction;

	/** @var string  JavaScript event handler name */
	public $toggleFunction;

	/** @var string  JavaScript code */
	public $doAlert = 'if (element) element.focus(); alert(message);';

	/** @var string  JavaScript code */
	public $doToggle = 'if (element) element.style.display = visible ? "" : "none";';

	/** @var string */
	public $validateScript;

	/** @var string */
	public $toggleScript;

	/** @var bool */
	private $central;

	/** @var Form */
	private $form;



	public function __construct(Form $form)
	{
		$this->form = $form;
		$name = ucfirst($form->getName()); //ucfirst(strtr($form->getUniqueId(), Form::NAME_SEPARATOR, '_'));
		$this->validateFunction = 'validate' . $name;
		$this->toggleFunction = 'toggle' . $name;
	}



	public function enable()
	{
		$this->validateScript = '';
		$this->toggleScript = '';
		$this->central = TRUE;

		foreach ($this->form->getControls() as $control) {
			$script = $this->getValidateScript($control->getRules());
			if ($script) {
				$this->validateScript .= "do {\n\t$script} while(0);\n\n\t";
			}
			$this->toggleScript .= $this->getToggleScript($control->getRules());

			if ($control instanceof ISubmitterControl && $control->getValidationScope() !== TRUE) {
				$this->central = FALSE;
			}
		}

		if ($this->validateScript || $this->toggleScript) {
			if ($this->central) {
				$this->form->getElementPrototype()->onsubmit("return $this->validateFunction(this)", TRUE);

			} else {
				foreach ($this->form->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $control) {
					if ($control->getValidationScope()) {
						$control->getControlPrototype()->onclick("return $this->validateFunction(this)", TRUE);
					}
				}
			}
		}
	}



	/**
	 * Generates the client side validation script.
	 * @return string
	 */
	public function renderClientScript()
	{
		$s = '';

		if ($this->validateScript) {
			$s .= "function $this->validateFunction(sender) {\n\t"
			. "var element, message, res;\n\t"
			. $this->validateScript
			. "return true;\n"
			. "}\n\n";
		}

		if ($this->toggleScript) {
			$s .= "function $this->toggleFunction(sender) {\n\t"
			. "var element, visible, res;\n\t"
			. $this->toggleScript
			. "\n}\n\n"
			. "$this->toggleFunction(null);\n";
		}

		if ($s) {
			return "<script type=\"text/javascript\">\n"
			. "/* <![CDATA[ */\n"
			. $s
			. "/* ]]> */\n"
			. "</script>";
		}
	}



	private function getValidateScript(Rules $rules, $onlyCheck = FALSE)
	{
		$res = '';
		foreach ($rules as $rule) {
			if (!is_string($rule->operation)) continue;

			if (strcasecmp($rule->operation, 'Nette\Forms\InstantClientScript::javascript') === 0) {
				$res .= "$rule->arg\n\t";
				continue;
			}

			$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
			if (!$script) continue;

			if (!empty($rule->message)) { // this is rule
				if ($onlyCheck) {
					$res .= "$script\n\tif (" . ($rule->isNegative ? '' : '!') . "res) { return false; }\n\t";

				} else {
					$res .= "$script\n\t"
						. "if (" . ($rule->isNegative ? '' : '!') . "res) { "
						. "message = " . json_encode((string) vsprintf($rule->control->translate($rule->message, is_int($rule->arg) ? $rule->arg : NULL), (array) $rule->arg)) . "; "
						. $this->doAlert
						. " return false; }\n\t";
				}
			}

			if ($rule->type === Rule::CONDITION) { // this is condition
				$innerScript = $this->getValidateScript($rule->subRules, $onlyCheck);
				if ($innerScript) {
					$res .= "$script\n\tif (" . ($rule->isNegative ? '!' : '') . "res) {\n\t\t" . str_replace("\n\t", "\n\t\t", rtrim($innerScript)) . "\n\t}\n\t";
					if (!$onlyCheck && $rule->control instanceof ISubmitterControl) {
						$this->central = FALSE;
					}
				}
			}
		}
		return $res;
	}



	private function getToggleScript(Rules $rules, $cond = NULL)
	{
		$s = '';
		foreach ($rules->getToggles() as $id => $visible) {
			$s .= "visible = true; {$cond}element = document.getElementById('" . $id . "');\n\t"
				. ($visible ? '' : 'visible = !visible; ')
				. $this->doToggle
				. "\n\t";
		}
		foreach ($rules as $rule) {
			if ($rule->type === Rule::CONDITION && is_string($rule->operation)) {
				$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
				if ($script) {
					$res = $this->getToggleScript($rule->subRules, $cond . "$script visible = visible && " . ($rule->isNegative ? '!' : '') . "res;\n\t");
					if ($res) {
						$el = $rule->control->getControlPrototype();
						if ($el->getName() === 'select') {
							$el->onchange("$this->toggleFunction(this)", TRUE);
						} else {
							$el->onclick("$this->toggleFunction(this)", TRUE);
							//$el->onkeyup("$this->toggleFunction(this)", TRUE);
						}
						$s .= $res;
					}
				}
			}
		}
		return $s;
	}



	private function getValueScript(IFormControl $control)
	{
		$tmp = "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\t";
		switch (TRUE) {
		case $control instanceof Checkbox:
			return $tmp . "var val = element.checked;\n\t";

		case $control instanceof RadioList:
			return "for (var val=null, i=0; i<" . count($control->getItems()) . "; i++) {\n\t\t"
			. "element = document.getElementById(" . json_encode($control->getHtmlId() . '-') . "+i);\n\t\t"
			. "if (element.checked) { val = element.value; break; }\n\t"
			. "}\n\t";

		default:
			return $tmp . "var val = element.value.replace(/^\\s+|\\s+\$/g, '');\n\t";
		}
	}



	private function getClientScript(IFormControl $control, $operation, $arg)
	{
		$operation = strtolower($operation);
		switch (TRUE) {
		case $control instanceof HiddenField || $control->isDisabled():
			return NULL;

		case $operation === ':filled' && $control instanceof RadioList:
			return $this->getValueScript($control) . "res = val !== null;";

		case $operation === ':submitted' && $control instanceof SubmitButton:
			return "element=null; res=sender && sender.name==" . json_encode($control->getHtmlName()) . ";";

		case $operation === ':equal' && $control instanceof MultiSelectBox:
			$tmp = array();
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				$tmp[] = "element.options[i].value==" . json_encode((string) $item);
			}
			$first = $control->isFirstSkipped() ? 1 : 0;
			return "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\tres = false;\n\t"
				. "for (var i=$first;i<element.options.length;i++)\n\t\t"
				. "if (element.options[i].selected && (" . implode(' || ', $tmp) . ")) { res = true; break; }";

		case $operation === ':filled' && $control instanceof SelectBox:
			return "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\t"
				. "res = element.selectedIndex >= " . ($control->isFirstSkipped() ? 1 : 0) . ";";

		case $operation === ':filled' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = val!='' && val!=" . json_encode((string) $control->getEmptyValue()) . ";";

		case $operation === ':minlength' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = val.length>=" . (int) $arg . ";";

		case $operation === ':maxlength' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = val.length<=" . (int) $arg . ";";

		case $operation === ':length' && $control instanceof TextBase:
			if (!is_array($arg)) {
				$arg = array($arg, $arg);
			}
			return $this->getValueScript($control) . "res = " . ($arg[0] === NULL ? "true" : "val.length>=" . (int) $arg[0]) . " && "
				. ($arg[1] === NULL ? "true" : "val.length<=" . (int) $arg[1]) . ";";

		case $operation === ':email' && $control instanceof TextBase:
			return $this->getValueScript($control) . 'res = /^[^@\s]+@[^@\s]+\.[a-z]{2,10}$/i.test(val);';

		case $operation === ':url' && $control instanceof TextBase:
			return $this->getValueScript($control) . 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val);';

		case $operation === ':regexp' && $control instanceof TextBase:
			if (strncmp($arg, '/', 1)) {
				throw new /*\*/InvalidStateException("Regular expression '$arg' must be JavaScript compatible.");
			}
			return $this->getValueScript($control) . "res = $arg.test(val);";

		case $operation === ':integer' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = /^-?[0-9]+$/.test(val);";

		case $operation === ':float' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val);";

		case $operation === ':range' && $control instanceof TextBase:
			return $this->getValueScript($control) . "res = " . ($arg[0] === NULL ? "true" : "parseFloat(val)>=" . json_encode((float) $arg[0])) . " && "
				. ($arg[1] === NULL ? "true" : "parseFloat(val)<=" . json_encode((float) $arg[1])) . ";";

		case $operation === ':filled' && $control instanceof FormControl:
			return $this->getValueScript($control) . "res = val!='';";

		case $operation === ':valid' && $control instanceof FormControl:
			return $this->getValueScript($control) . "res = function(){\n\t" . $this->getValidateScript($control->getRules(), TRUE) . "return true; }();";

		case $operation === ':equal' && $control instanceof FormControl:
			if ($control instanceof Checkbox) $arg = (bool) $arg;
			$tmp = array();
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if ($item instanceof IFormControl) { // compare with another form control?
					$tmp[] = "val==function(){var element;" . $this->getValueScript($item). "return val;}()";
				} else {
					$tmp[] = "val==" . json_encode($item);
				}
			}
			return $this->getValueScript($control) . "res = (" . implode(' || ', $tmp) . ");";
		}
	}



	public static function javascript()
	{
		return TRUE;
	}

}
