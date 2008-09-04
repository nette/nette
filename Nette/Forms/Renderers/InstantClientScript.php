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
 * Instant validation JavaScript generator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
final class InstantClientScript extends /*Nette::*/Object
{
	/** @var string  JavaScript event handler name */
	public $validateFunction;

	/** @var string  JavaScript event handler name */
	public $toggleFunction;

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
			$this->validateScript .= $this->getValidateScript($control->getRules());
			$this->toggleScript .= $this->getToggleScript($control->getRules());
		}

		if ($this->validateScript || $this->toggleScript) {
			if ($this->central) {
				$this->form->getElementPrototype()->onsubmit = "return " . $this->validateFunction . "(this)";

			} else {
				foreach ($this->form->getComponents(TRUE, 'Nette::Forms::ISubmitterControl') as $control) {
					$control->getControlPrototype()->onclick .= 'return ' . $this->validateFunction . "(this);";
				}
			}
		}
	}



	/**
	 * Genetares the client side validation script.
	 * @return string
	 */
	public function renderClientScript()
	{
		$s = '';

		if ($this->validateScript) {
			$s .= "function $this->validateFunction(sender) {\n\t"
			. "var el, res;\n\t"
			. $this->validateScript
			. "return true;\n"
			. "}\n\n";
		}

		if ($this->toggleScript) {
			$s .= "function $this->toggleFunction(sender) {\n\t"
			. "var el, res, resT;\n\t"
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
			$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
			if (!$script) continue;
			$res .= "$script\n\t";

			if (!empty($rule->message)) { // this is rule
				if ($onlyCheck) {
					$res .= "if (" . ($rule->isNegative ? '' : '!') . "res) { return false; }\n\t";
				} else {
					$translator = $rule->control->getTranslator();
					$message = $translator === NULL ? $rule->message : $translator->translate($rule->message);
					$res .= "if (" . ($rule->isNegative ? '' : '!') . "res) { " .
						"if (el) el.focus(); alert(" . json_encode((string) vsprintf($message, (array) $rule->arg)) . "); return false; }\n\t";
				}
			}

			if ($rule->isCondition) { // this is condition
				$script = $this->getValidateScript($rule->subRules, $onlyCheck);
				if ($script) {
					$res .= "if (" . ($rule->isNegative ? '!' : '') . "res) {\n\t" . $script . "}\n\t";
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
			$s .= "resT = true; {$cond}el = document.getElementById('" . $id . "');\n\t"
				. "if (el) el.style.display = " . ($visible ? '' : '!') . "resT ? 'block' : 'none';\n\t";
		}
		foreach ($rules as $rule) {
			if ($rule->isCondition && is_string($rule->operation)) {
				$script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
				if ($script) {
					$res = $this->getToggleScript($rule->subRules, $cond . "$script resT = resT && res;\n\t");
					if ($res) {
						$el = $rule->control->getControlPrototype();
						if ($el->getName() === 'select') {
							$el->onchange .= $this->toggleFunction . "(this);";
						} else {
							$el->onclick .= $this->toggleFunction . "(this);";
							//$el->onkeyup .= $this->toggleFunction . "(this);";
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
		//TODO: jinak!
		$operation = str_replace('textarea::', 'textbase::', $operation);
		$operation = str_replace('textinput::', 'textbase::', $operation);
		if (substr($operation, -15) === '::validatevalid') $operation = '::validatevalid';

		// trim for TextBase
		$id = $control->getHtmlId();
		$tmp = "el = document.getElementById('" . $id . "');\n\t";
		$tmp2 = "var val = el.value.replace(/^\\s+/, '').replace(/\\s+\$/, '');\n\t";

		switch ($operation) {
		case /*nette::forms::*/'instantclientscript::javascript':
			return $arg;

		case '::validatevalid':
			return $tmp . $tmp2 . "res = function(){\n\t" . $this->getValidateScript($control->getRules(), TRUE) . "return true; }();";

		case /*nette::forms::*/'checkbox::validateequal':
			return "el = document.getElementById('" . $id . "');\n\tres = " . ($arg ? '' : '!') . "el.checked;";

		case /*nette::forms::*/'radiolist::validateequal':
			return "res = false;\n\t" .
				"for (var i=0;i<" . count($control->getItems()) . ";i++) {\n\t\t" .
				"el = document.getElementById('" . $id . "-'+i);\n\t\t" .
				"if (el.checked && el.value==" . json_encode((string) $arg) . ") { res = true; break; }\n\t" .
				"}\n\tel = null;";

		case /*nette::forms::*/'radiolist::validatefilled':
			return "res = false; el=null;\n\t" .
				"for (var i=0;i<" . count($control->getItems()) . ";i++) " .
				"if (document.getElementById('" . $id . "-'+i).checked) { res = true; break; }";

		case /*nette::forms::*/'submitbutton::validatesubmitted':
			return "el=null; res=sender && sender.name==" . json_encode($control->getHtmlName()) . ";";

		case /*nette::forms::*/'selectbox::validateequal':
			$first = $control->isFirstSkipped() ? 1 : 0;
			return $tmp . "res = false;\n\t" .
				"for (var i=$first;i<el.options.length;i++)\n\t\t" .
				"if (el.options[i].selected && el.options[i].value==" . json_encode((string) $arg) . ") { res = true; break; }";

		case /*nette::forms::*/'selectbox::validatefilled':
			$first = $control->isFirstSkipped() ? 1 : 0;
			return $tmp . "res = el.selectedIndex >= $first;";

		case /*nette::forms::*/'textbase::validateequal':
			if (is_object($arg)) { // compare with another form control?
				return get_class($arg) === $control->getClass()
					? $tmp . $tmp2 . "res = val==document.getElementById('" . $arg->getHtmlId() . "').value;" // missing trim
					: 'res = false;';
			} else {
				return $tmp . $tmp2 . "res = val==" . json_encode((string) $arg) . ";";
			}

		case /*nette::forms::*/'textbase::validatefilled':
			return $tmp . $tmp2 . "res = val!='' && val!=" . json_encode((string) $control->getEmptyValue()) . ";";

		case /*nette::forms::*/'textbase::validateminlength':
			return $tmp . $tmp2 . "res = val.length>=" . (int) $arg . ";";

		case /*nette::forms::*/'textbase::validatemaxlength':
			return $tmp . $tmp2 . "res = val.length<=" . (int) $arg . ";";

		case /*nette::forms::*/'textbase::validatelength':
			if (!is_array($arg)) {
				$arg = array($arg, $arg);
			}
			return $tmp . $tmp2 . "res = val.length>=" . (int) $arg[0] . " && val.length<=" . (int) $arg[1] . ";";

		case /*nette::forms::*/'textbase::validateemail':
			return $tmp . $tmp2 . 'res = /^[^@]+@[^@]+\.[a-z]{2,6}$/i.test(val);';

		case /*nette::forms::*/'textbase::validateurl':
			return $tmp . $tmp2 . 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val);';

		case /*nette::forms::*/'textbase::validateregexp':
			return $tmp . $tmp2 . "res = $arg.test(val);";

		case /*nette::forms::*/'textbase::validatenumeric':
			return $tmp . $tmp2 . "res = /^-?[0-9]+$/.test(val);";

		case /*nette::forms::*/'textbase::validatefloat':
			return $tmp . $tmp2 . "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val);";

		case /*nette::forms::*/'textbase::validaterange':
			return $tmp . $tmp2 . "res = parseFloat(val)>=" . (float) $arg[0] . " && parseFloat(val)<=" . (float) $arg[1] . ";";
		}
	}



	public static function javascript()
	{
		// TODO: needed?
		return TRUE;
	}

}
