<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */

namespace Nette\Forms;

use Nette;



/**
 * Instant validation JavaScript generator.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
final class InstantClientScript extends Nette\Object
{
	/** @var array */
	private $validateScripts;

	/** @var string */
	private $toggleScript;

	/** @var bool */
	private $central;

	/** @var Form */
	private $form;



	public function __construct(Form $form)
	{
		$this->form = $form;
	}



	public function enable()
	{
		$this->validateScripts = array();
		$this->toggleScript = '';
		$this->central = TRUE;

		foreach ($this->form->getControls() as $control) {
			$script = $this->getValidateScript($control->getRules());
			if ($script) {
				$this->validateScripts[$control->getHtmlName()] = $script;
			}
			$this->toggleScript .= $this->getToggleScript($control->getRules());

			if ($control instanceof ISubmitterControl && $control->getValidationScope() !== TRUE) {
				$this->central = FALSE;
			}
		}

		if ($this->validateScripts || $this->toggleScript) {
			if ($this->central) {
				$this->form->getElementPrototype()->onsubmit("return nette.validateForm(this)", TRUE);

			} else {
				foreach ($this->form->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $control) {
					if ($control->getValidationScope()) {
						$control->getControlPrototype()->onclick("return nette.validateForm(this)", TRUE);
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
		if (!$this->validateScripts && !$this->toggleScript) {
			return;
		}

		$formName = json_encode((string) $this->form->getElementPrototype()->id);
		ob_start();
		include __DIR__ . '/InstantClientScript.phtml';
		return ob_get_clean();
	}



	private function getValidateScript(Rules $rules)
	{
		$res = '';
		foreach ($rules as $rule) {
			if (!is_string($rule->operation)) continue;

			if (strcasecmp($rule->operation, 'Nette\\Forms\\InstantClientScript::javascript') === 0) {
				$res .= "$rule->arg\n";
				continue;
			}

			$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
			if (!$script) continue;

			if (!empty($rule->message)) { // this is rule
				$message = Rules::formatMessage($rule, FALSE);
				$res .= "$script\n"
					. "if (" . ($rule->isNegative ? '' : '!') . "res) "
					. "return " . json_encode((string) $message) . (strpos($message, '%value') === FALSE ? '' : ".replace('%value', val);\n") . ";\n";
			}

			if ($rule->type === Rule::CONDITION) { // this is condition
				$innerScript = $this->getValidateScript($rule->subRules);
				if ($innerScript) {
					$res .= "$script\nif (" . ($rule->isNegative ? '!' : '') . "res) {\n" . Nette\String::indent($innerScript) . "}\n";
					if ($rule->control instanceof ISubmitterControl) {
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
			$s .= "visible = true; {$cond}\n"
				. "nette.toggle(" . json_encode((string) $id) . ", " . ($visible ? '' : '!') . "visible);\n";
		}
		$formName = json_encode((string) $this->form->getElementPrototype()->id);
		foreach ($rules as $rule) {
			if ($rule->type === Rule::CONDITION && is_string($rule->operation)) {
				$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
				if ($script) {
					$res = $this->getToggleScript($rule->subRules, $cond . "$script visible = visible && " . ($rule->isNegative ? '!' : '') . "res;\n");
					if ($res) {
						$el = $rule->control->getControlPrototype();
						if ($el->getName() === 'select') {
							$el->onchange("nette.forms[$formName].toggle(this)", TRUE);
						} else {
							$el->onclick("nette.forms[$formName].toggle(this)", TRUE);
							//$el->onkeyup("nette.forms[$formName].toggle(this)", TRUE);
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
		$operation = strtolower($operation);
		$elem = 'form[' . json_encode($control->getHtmlName()) . ']';

		switch (TRUE) {
		case $control instanceof HiddenField || $control->isDisabled():
			return NULL;

		case $operation === ':filled' && $control instanceof RadioList:
			return "res = (val = nette.getValue($elem)) !== null;";

		case $operation === ':submitted' && $control instanceof SubmitButton:
			return "res = sender && sender.name==" . json_encode($control->getHtmlName()) . ";";

		case $operation === ':equal' && $control instanceof MultiSelectBox:
			$tmp = array();
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				$tmp[] = "options[i].value==" . json_encode((string) $item);
			}
			$first = $control->isFirstSkipped() ? 1 : 0;
			return "var options = $elem.options; res = false;\n"
				. "for (var i=$first, len=options.length; i<len; i++)\n\t"
				. "if (options[i].selected && (" . implode(' || ', $tmp) . ")) { res = true; break; }";

		case $operation === ':filled' && $control instanceof SelectBox:
			return "res = $elem.selectedIndex >= " . ($control->isFirstSkipped() ? 1 : 0) . ";";

		case $operation === ':filled' && $control instanceof TextBase:
			return "val = nette.getValue($elem); res = val!='' && val!=" . json_encode((string) $control->getEmptyValue()) . ";";

		case $operation === ':minlength' && $control instanceof TextBase:
			return "res = (val = nette.getValue($elem)).length>=" . (int) $arg . ";";

		case $operation === ':maxlength' && $control instanceof TextBase:
			return "res = (val = nette.getValue($elem)).length<=" . (int) $arg . ";";

		case $operation === ':length' && $control instanceof TextBase:
			if (!is_array($arg)) {
				$arg = array($arg, $arg);
			}
			return "val = nette.getValue($elem); res = " . ($arg[0] === NULL ? "true" : "val.length>=" . (int) $arg[0]) . " && "
				. ($arg[1] === NULL ? "true" : "val.length<=" . (int) $arg[1]) . ";";

		case $operation === ':email' && $control instanceof TextBase:
			return 'res = /^[^@\s]+@[^@\s]+\.[a-z]{2,10}$/i.test(val = nette.getValue('.$elem.'));';

		case $operation === ':url' && $control instanceof TextBase:
			return 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val = nette.getValue('.$elem.'));';

		case $operation === ':regexp' && $control instanceof TextBase:
			if (!preg_match('#^(/.*/)([imu]*)$#', $arg, $matches)) {
				return NULL; // regular expression must be JavaScript compatible
			}
			$arg = $matches[1] . str_replace('u', '', $matches[2]);
			return "res = $arg.test(val = nette.getValue($elem));";

		case $operation === ':integer' && $control instanceof TextBase:
			return "res = /^-?[0-9]+$/.test(val = nette.getValue($elem));";

		case $operation === ':float' && $control instanceof TextBase:
			return "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val = nette.getValue($elem));";

		case $operation === ':range' && $control instanceof TextBase:
			return "val = nette.getValue($elem); res = " . ($arg[0] === NULL ? "true" : "parseFloat(val)>=" . json_encode((float) $arg[0])) . " && "
				. ($arg[1] === NULL ? "true" : "parseFloat(val)<=" . json_encode((float) $arg[1])) . ";";

		case $operation === ':filled' && $control instanceof FormControl:
			return "res = (val = nette.getValue($elem)) != '';";

		case $operation === ':valid' && $control instanceof FormControl:
			return "res = !this[" . json_encode($control->getHtmlName()) . "](sender);";

		case $operation === ':equal' && $control instanceof FormControl:
			if ($control instanceof Checkbox) $arg = (bool) $arg;
			$tmp = array();
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if ($item instanceof IFormControl) { // compare with another form control?
					$tmp[] = "val==nette.getValue(form[" . json_encode($item->getHtmlName()) . "])";
				} else {
					$tmp[] = "val==" . json_encode($item);
				}
			}
			return "val = nette.getValue($elem); res = (" . implode(' || ', $tmp) . ");";
		}
	}



	public static function javascript()
	{
		return TRUE;
	}

}
