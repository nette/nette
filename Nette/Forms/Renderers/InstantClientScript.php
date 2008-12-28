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
 * @package    Nette\Forms
 * @version    $Id$
 */

/*namespace Nette\Forms;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Instant validation JavaScript generator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
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
						. "message = " . json_encode((string) vsprintf($rule->control->translate($rule->message), (array) $rule->arg)) . "; "
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



	private function getClientScript(IFormControl $control, $operation, $arg)
	{
		$id = $control->getHtmlId();
		$tmp = "element = document.getElementById('" . $id . "');\n\t";
		$tmp2 = "var val = element.value.replace(/^\\s+/, '').replace(/\\s+\$/, '');\n\t";
		$tmp3 = array();
		$operation = strtolower($operation);

		switch (TRUE) {
		case $control instanceof HiddenField || $control->isDisabled():
			return NULL;

		case $operation === ':equal' && $control instanceof Checkbox:
			return $tmp . "res = " . ($arg ? '' : '!') . "element.checked;";

		case $operation === ':filled' && $control instanceof FileUpload:
			return $tmp . "res = element.value!='';";

		case $operation === ':equal' && $control instanceof RadioList:
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				$tmp3[] = "element.value==" . json_encode((string) $item);
			}
			return "res = false;\n\t"
				. "for (var i=0;i<" . count($control->getItems()) . ";i++) {\n\t\t"
				. "element = document.getElementById('" . $id . "-'+i);\n\t\t"
				. "if (element.checked && (" . implode(' || ', $tmp3) . ")) { res = true; break; }\n\t"
				. "}\n\telement = null;";

		case $operation === ':filled' && $control instanceof RadioList:
			return "res = false; element=null;\n\t"
				. "for (var i=0;i<" . count($control->getItems()) . ";i++) "
				. "if (document.getElementById('" . $id . "-'+i).checked) { res = true; break; }";

		case $operation === ':submitted' && $control instanceof SubmitButton:
			return "element=null; res=sender && sender.name==" . json_encode($control->getHtmlName()) . ";";

		case $operation === ':equal' && $control instanceof SelectBox:
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				$tmp3[] = "element.options[i].value==" . json_encode((string) $item);
			}
			$first = $control->isFirstSkipped() ? 1 : 0;
			return $tmp . "res = false;\n\t"
				. "for (var i=$first;i<element.options.length;i++)\n\t\t"
				. "if (element.options[i].selected && (" . implode(' || ', $tmp3) . ")) { res = true; break; }";

		case $operation === ':filled' && $control instanceof SelectBox:
			$first = $control->isFirstSkipped() ? 1 : 0;
			return $tmp . "res = element.selectedIndex >= $first;";

		case $operation === ':filled' && $control instanceof TextInput:
			return $tmp . $tmp2 . "res = val!='' && val!=" . json_encode((string) $control->getEmptyValue()) . ";";

		case $operation === ':minlength' && $control instanceof TextBase:
			return $tmp . $tmp2 . "res = val.length>=" . (int) $arg . ";";

		case $operation === ':maxlength' && $control instanceof TextBase:
			return $tmp . $tmp2 . "res = val.length<=" . (int) $arg . ";";

		case $operation === ':length' && $control instanceof TextBase:
			if (!is_array($arg)) {
				$arg = array($arg, $arg);
			}
			return $tmp . $tmp2 . "res = " . ($arg[0] === NULL ? "true" : "val.length>=" . (int) $arg[0]) . " && "
				. ($arg[1] === NULL ? "true" : "val.length<=" . (int) $arg[1]) . ";";

		case $operation === ':email' && $control instanceof TextBase:
			return $tmp . $tmp2 . 'res = /^[^@]+@[^@]+\.[a-z]{2,6}$/i.test(val);';

		case $operation === ':url' && $control instanceof TextBase:
			return $tmp . $tmp2 . 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val);';

		case $operation === ':regexp' && $control instanceof TextBase:
			if (strncmp($arg, '/', 1)) {
				throw new /*\*/InvalidStateException("Regular expression '$arg' must be JavaScript compatible.");
			}
			return $tmp . $tmp2 . "res = $arg.test(val);";

		case $operation === ':integer' && $control instanceof TextBase:
			return $tmp . $tmp2 . "res = /^-?[0-9]+$/.test(val);";

		case $operation === ':float' && $control instanceof TextBase:
			return $tmp . $tmp2 . "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val);";

		case $operation === ':range' && $control instanceof TextBase:
			return $tmp . $tmp2 . "res = " . ($arg[0] === NULL ? "true" : "parseFloat(val)>=" . json_encode((float) $arg[0])) . " && "
				. ($arg[1] === NULL ? "true" : "parseFloat(val)<=" . json_encode((float) $arg[1])) . ";";

		case $operation === ':filled' && $control instanceof FormControl:
			return $tmp . $tmp2 . "res = val!='';";

		case $operation === ':valid' && $control instanceof FormControl:
			return $tmp . $tmp2 . "res = function(){\n\t" . $this->getValidateScript($control->getRules(), TRUE) . "return true; }();";

		case $operation === ':equal' && $control instanceof FormControl:
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if (is_object($item)) { // compare with another form control?
					$tmp3[] = get_class($item) === $control->getClass()
						? "val==document.getElementById('" . $item->getHtmlId() . "').value" // missing trim
						: 'false';
				} else {
					$tmp3[] = "val==" . json_encode((string) $item);
				}
			}
			return $tmp . $tmp2 . "res = (" . implode(' || ', $tmp3) . ");";
		}
	}



	public static function javascript()
	{
		return TRUE;
	}

}
