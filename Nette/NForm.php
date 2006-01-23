<?php

/**
 * This file is part of the Nette Framework (http://php7.org/nette/)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004-2007 David Grudl aka -dgx- (http://www.dgx.cz)
 * @license    New BSD License
 * @version    $Revision: 89 $ $Date: 2007-10-25 08:17:37 +0200 (čt, 25 X 2007) $
 * @category   Nette
 * @package    Nette-Form
 */



/**
 * NForm - allowes create, validate and render (X)HTML forms
 *
 * Requirements:
 *     - PHP 5.0.0 (iconv_strlen in NFormItems requires PHP 5.0.5)
 *     - NHtml
 *     - NComponent
 *     - NException
 */
class NForm extends NComponent
{
    const
        EQUAL = 10,
        FILLED = 11,
        SUBMITTED = 12,
        MINLENGTH = 13,
        MAXLENGTH = 14,
        LENGTH = 15,
        EMAIL = 16,
        URL = 17,
        REGEXP = 18,
        NUMERIC = 19,
        FLOAT = 20,
        RANGE = 21,
        MAXFILESIZE = 22,
        MIMETYPE = 23;

    const TRACKER_ID = '_form_';

    const VM_CENTRAL = 1;
    const VM_BUTTON = 3;

    /** @var NHtml  <form> element */
    private $el;

    /** @var array */
    private $values;

    /** @var array */
    private $submittedRaw;

    /** @var string|bool */
    private $submittedBy;

    /** @var NFormRules */
    protected $rules;

    /** @var array */
    protected $errors = array();

    /** @var string  JavaScript event handlers names */
    public $validateFunction, $toggleFunction;

    /** @var string  0 (none), VM_CENTRAL, VM_BUTTON */
    public $validateMode = 0;

    /** @var callback  user handler */
//    public $onSubmit;

    /** @var callback  user handler */
//    public $onCreate;

    /**
     * Form constructor
     * @param    string    form's name.
     * @param    string    HTTP method, defaults to post
     */
    public function __construct($name=NULL, $method='post')
    {
        $this->name = $name;
        $this->el = NHtml::el('form');
        $this->el->method = $method;
        $this->el->action = $_SERVER['REQUEST_URI']; // NHttpRequest::getUri();
        $this->rules = new NFormRules($this);
        $this->validateFunction = 'validate' . $name;
        $this->toggleFunction = 'toggle' . $name;

        $this->processHttpRequest();

        // tracker
        if ($name != NULL) {
            $tracker = new NFormHiddenItem($this, self::TRACKER_ID);
            $this->addComponent($tracker, self::TRACKER_ID);
            $tracker->setValue($name);
        }
    }



    /**
     * Inserts a child component to this container
     * @param NComponent
     * @param string
     * @return void
     */
    protected function addComponent(NComponent $child, $name)
    {
        parent::addComponent($child, $name);
        $child->setHttpValue($this->submittedRaw);
        if ($child->isSubmitted()) $this->submittedBy = $name;
    }



    /**
     * Process HTTP request data
     * @return void
     */
    public function processHttpRequest()
    {
        $this->submittedBy = FALSE;
        $this->submittedRaw = array();

        $method = $_SERVER['REQUEST_METHOD']; // NHttpRequest::getMethod();
        if (strcasecmp($method, $this->el->method) === 0)
        {
            if (strcasecmp('get', $method) === 0) {
                $this->submittedBy =
                    $this->name == NULL
                    ? !empty($_GET)
                    : isset($_GET[self::TRACKER_ID]) && ($_GET[self::TRACKER_ID] === $this->name);

                if ($this->submittedBy) $this->submittedRaw = $_GET;

            } else {
                $this->submittedBy =
                    $this->name == NULL
                    ? !empty($_POST) || !empty($_FILES)
                    : isset($_POST[self::TRACKER_ID]) && ($_POST[self::TRACKER_ID] === $this->name);

                if ($this->submittedBy) $this->submittedRaw = $_POST + $_FILES;
            }
        }

        // update items
        foreach ($this->getComponents() as $name => $item) {
            /** @var NFormItem */
            $item->setHttpValue($this->submittedRaw);
            if ($item->isSubmitted()) $this->submittedBy = $name;
        }

        // but keep tracker
        if (isset($this[self::TRACKER_ID])) { die('jo');
            $this[self::TRACKER_ID]->setValue($this->name);
        }
    }



    /**
     * Sets form's action
     * @param  string URL
     * @return void
     */
    public function setAction($URL)
    {
        // support for Nette framework
        if (is_object($URL)) $URL = $URL->getUri();
        if (class_exists('NRequest', FALSE)) {
            if ($URL instanceof NRequest) $URL = $URL->getUri();
        }
        return $this->el->action = $URL;
    }



    /**
     * Initializes default form values
     * @param     array    values used to fill the form
     * @return    void
     */
    public function setDefaults($values)
    {
        foreach ($values as $name => $value)
            if (isset($this[$name]))
                $this[$name]->setValue($value);

    }



    /**
     * Returns the values submitted by the form
     * @return    array
     */
    public function getSubmitted()
    {
        if (!$this->submittedBy) return NULL;

        if ($this->values === NULL) { // lazy init
            $this->values = array();

            foreach ($this->getComponents() as $key => $item)
                if ($item instanceof NFormItem)
                    $this->values[$key] = $item->getValue();
        }

        return $this->values;
    }


   /**
    * Tells if the form was submitted
    * @return string|bool
    */
    public function isSubmitted($by = NULL)
    {
        return $by === NULL
            ? $this->submittedBy
            : $this->submittedBy === $by;
    }



    /**
     * Adds a validation rule for the form item
     * @param    string     form item name
     * @param    string     rule type
     * @param    string     message to display for invalid data
     * @param    mixed      optional extra rule data
     * @return   void
     */
    public function addRule($name, $operation, $message, $arg = NULL)
    {
        $this->rules->addRule($name, $operation, $message, $arg);
    }



    /**
     * Adds a validation condition based on item's state
     * @param    string     form item name
     * @param    string     condition type
     * @param    mixed      optional condition data
     * @param    string     optional HTML #ID to be toggled
     * @return   NFormRules
     */
    public function addCondition($name, $operation, $value = NULL, $toggle = NULL)
    {
        return $this->rules->addCondition($name, $operation, $value, $toggle);
    }



    /**
     * Performs the server side validation
     * @return    boolean   is valid?
     */
    public function validate()
    {
        if ($this->errors) return FALSE; // validated yet ...and is not valid

        // validate!
        return $this->rules->validate();
    }



    /**
     * Adds error message to the list
     * @param  string  error message
     * @param  string  item's name or NULL for global errors
     * @return void
     */
    public function addError($message, $name = NULL)
    {
        if ($name !== NULL) $this->errors[$name][] = $message;
        $this->errors[NULL][] = $message;
    }



    /**
     * Returns errors corresponding to form or validated item
     * @param  string  item's name or NULL for global errors
     * @return array
     */
    public function getErrors($name = NULL)
    {
        return isset($this->errors[$name])
            ? $this->errors[$name]
            : NULL;
    }



    /**
     * @return bool
     */
    public function hasErrors($name = NULL)
    {
        return isset($this->errors[$name])
            ? (bool) $this->errors[$name]
            : FALSE;
    }



    /**
     * Returns form's element
     * @return NHtml
     */
    public function getFormElement()
    {
        if ($this->validateMode === self::VM_CENTRAL)
            $this->el->onsubmit = "return " . $this->validateFunction . "(this)";

        return $this->el;
    }



    public function addText($name, $label, $size = NULL, $maxlength = NULL)
    {
        $item = new NFormTextItem($this, $name, $label, FALSE);
        $item->control->size = $size;
        $item->control->maxlength = $maxlength;
        $item->control->class[] = 'text';
        return $this[$name] = $item;
    }



    public function addPassword($name, $label, $size = NULL, $maxlength = NULL)
    {
        $item = new NFormTextItem($this, $name, $label, TRUE);
        $item->control->size = $size;
        $item->control->maxlength = $maxlength;
        $item->control->class[] = 'text';
        return $this[$name] = $item;
    }



    public function addTextArea($name, $label, $cols, $rows)
    {
        return $this[$name] = new NFormTextAreaItem($this, $name, $label, $cols, $rows);
    }



    public function addFile($name, $label)
    {
        $this->el->enctype = 'multipart/form-data';
        $item = new NFormFileItem($this, $name, $label);
        $item->control->class[] = 'text';
        return $this[$name] = $item;
    }



    public function addHidden($name)
    {
        return $this[$name] = new NFormHiddenItem($this, $name);
    }



    public function addCheckbox($name, $label)
    {
        return $this[$name] = new NFormCheckboxItem($this, $name, $label);
    }



    public function addRadio($name, $label, $items)
    {
        return $this[$name] = new NFormRadioItem($this, $name, $label, $items);
    }



    public function addSelect($name, $label, $items, $multiple = FALSE, $size = 1)
    {
        $item = new NFormSelectItem($this, $name, $label, $items, $multiple);
        if ($size > 1) $item->control->size = $size;
        return $this[$name] = $item;
    }



    public function addSubmit($name, $label)
    {
        $item = new NFormButtonItem($this, $name, $label, TRUE);
        $item->control->class[] = 'button';
        return $this[$name] = $item;
    }



    public function addButton($name, $label)
    {
        $item = new NFormButtonItem($this, $name, $label, FALSE);
        $item->control->class[] = 'button';
        return $this[$name] = $item;
    }



    public function addImage($name, $src, $alt)
    {
        return $this[$name] = new NFormImageItem($this, $name, $src, $alt);
    }



    public function renderErrors($name = NULL)
    {
        if (empty($this->errors[$name])) return NULL;

        $ul = NHtml::el('ul')->class('error');
        foreach ($this->errors[$name] as $error)
            $ul->create('li', $error);

        return $ul;
    }



    public function renderBegin()
    {
        echo $this->getFormElement()->startTag();
    }



    public function renderEnd()
    {
        echo $this->getFormElement()->endTag();
        echo $this->renderClientScript();
    }



    /**
     * Genetares the client side validation script
     * @return void
     */
    public function renderClientScript()
    {
        // or check $this->validateMode ?
        $validateScript = $this->rules->getValidateScript();
        $toggleScript = $this->rules->getToggleScript();
        if (!$validateScript && !$toggleScript) return;

        echo "<script type=\"text/javascript\">\n",
            "/* <![CDATA[ */\n";

        if ($validateScript)
            echo
            "function $this->validateFunction(sender) {\n\t",
            "var el, res;\n\t",
            $validateScript,
            "return true;\n",
            "}\n\n";

        if ($toggleScript)
            echo
            "function $this->toggleFunction(sender) {\n\t",
            "var el, res;\n\t",
            $toggleScript,
            "\n}\n\n",
            "$this->toggleFunction(null);\n"; // !!! onload ?

        echo "/* ]]> */\n", "</script>\n";
    }



    public function renderForm()
    {
        $this->renderBegin();
        if ($this->isSubmitted()) echo "\n", $this->renderErrors();

        $hidden = NHtml::el('div');
        echo "\n<table>\n";
        foreach ($this->getComponents() as $item) {
            if ($item instanceof NFormHiddenItem) {
                $hidden->add($item->control);

            } elseif ($item instanceof NFormCheckboxItem) {
                echo "<tr>\n\t<th>&nbsp;</th>\n\t<td>", $item->control, $item->label, "</td>\n</tr>\n\n";

            } elseif ($item instanceof NFormItem) {
                echo "<tr>\n\t<th>", ($item->label ? $item->label : '&nbsp;'), "</th>\n\t<td>", $item->control, "</td>\n</tr>\n\n";
            }
        }
        echo "</table>\n";
        if ($hidden->count()) echo $hidden;

        $this->renderEnd();
    }

}







/**
 * Form validation rules
 */
class NFormRules extends NObject
{
    /** @var array */
    protected $rules = array();

    /** @var NForm */
    protected $form;

    /** @var string */
    public static $requiredClass = 'required';


    public function __construct(NForm $form)
    {
        $this->form = $form;
    }



    /**
     * Adds a validation rule for the form item
     * @param    string     form item name
     * @param    string     rule type
     * @param    string     message to display for invalid data
     * @param    mixed      optional extra rule data
     * @return   void
     */
    public function addRule($name, $operation, $message, $arg = NULL)
    {
        $item = $this->form[$name];
        $item->notifyRule(TRUE, $operation, $arg);
        if ($operation === NForm::FILLED && $item->label) {
            $item->label->class[] = self::$requiredClass;
        }
        $this->form->validateMode |= NForm::VM_CENTRAL;

        $this->rules[] = array(
            'item' => $item,
            'operation' => $operation < 0 ? ~ $operation : $operation,
            'arg' => $arg,
            'message' => vsprintf($message, (array) $arg),
            'toggle' => NULL,
            'subrules' => NULL,
            'neg' => $operation < 0,
            'script' => NULL,
        );
    }



    /**
     * Adds a validation condition based on item's state
     * @param    string     form item name
     * @param    string     condition type
     * @param    mixed      optional condition data
     * @param    string     optional HTML #ID to be toggled
     * @return   NFormRules
     */
    public function addCondition($name, $operation, $arg = NULL, $toggle = NULL)
    {
        $item = $this->form[$name];
        $item->notifyRule(FALSE, $operation, $arg);
        if ($toggle) $item->setEvent($this->form->toggleFunction . "(this);");

        $subrules = new self($this->form);
        $this->rules[] = array(
            'item' => $item,
            'operation' => $operation < 0 ? ~ $operation : $operation,
            'arg' => $arg,
            'message' => NULL,
            'toggle' => $toggle,
            'subrules' => $subrules,
            'neg' => $operation < 0,
            'script' => NULL,
        );
        return $subrules;
    }



    /**
     * Adds a user script
     * @param    string     script
     * @return   void
     */
    public function addScript($script)
    {
        $this->rules[] = array(
            'script' => $script,
        );
    }



    /**
     * Validates ruleset
     * @return   bool    is valid?
     */
    public function validate()
    {
        $valid = TRUE;
        foreach ($this->rules as $rule)
        {
            extract($rule);

            if ($script) continue; // this is user script

            $ok = ($neg xor $item->validate($operation, $arg));

            if (!$ok && $message) { // this is rule
                $this->form->addError($message, $item->getName());
                $valid = FALSE;
            }

            if ($ok && $subrules) { // this is condition
                $ok = $subrules->validate();
                $valid = $valid && $ok;
            }
        }
        return $valid;
    }



    public function getValidateScript()
    {
        $res = '';
        foreach ($this->rules as $rule) {
            extract($rule);

            if ($script) {  // this is user script
                $res .= $script . "\n\t";
                continue;
            }

            $script = $item->getClientScript($operation, $arg);
            if (!$script) continue;
            $res .= "$script\n\t";

            if ($message) { // this is rule
                $res .= "if (" . ($neg ? '' : '!') . "res) { " .
                    "if (el) el.focus(); alert('" . addslashes($message) . "'); return false; }\n\t";
            }

            if ($subrules) { // this is condition
                $script = $subrules->getValidateScript();
                if ($script) $res .= "if (" . ($neg ? '!' : '') . "res) {\n\t" . $script . "}\n\t";
            }
        }
        return $res;
    }



    public function getToggleScript()
    {
        $res = '';
        foreach ($this->rules as $rule) {
            extract($rule);

            if ($script) continue; // this is user script

            if ($toggle) {
                $script = $item->getClientScript($operation, $arg);
                if ($script) {
                    $res .= $script . "\n\t"
                        . "el = document.getElementById('" . $toggle . "');\n\t"
                        . "if (el) el.style.display = " . ($neg ? '!' : '') . "res ? 'block' : 'none';\n\t";
                }
            }

            if ($subrules) $res .= $subrules->getToggleScript();
        }
        return $res;
    }

}







/**
 * Form controls
 */
abstract class NFormItem extends NComponent
{
    /** @var NHtml */
    public $label;

    /** @var NHtml */
    public $control;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $id;

    /** @var int */
    public static $counter = 0;



    public function __construct(NComponent $parent, $name, $label=NULL)
    {
        $this->id = 'frm-' . ++self::$counter;
        $this->name = $name;
        $this->label = NHtml::el('label', array(
            'for' => $this->id,
        ))->setText($label);

        $this->control = NHtml::el('input', array(
            'id' => $this->id,
            'name' => $name,
        ));
    }



    public function setValue($value)
    {}


    public function setHttpValue($data)
    {
        $this->setValue(
            isset($data[$this->name]) ? $data[$this->name] : NULL
        );
    }



    public function getValue()
    {
        return $this->value;
    }



    public function setEvent($event)
    {
        $this->control->onclick = $event;
    }



    public function isSubmitted()
    {} // returns NULL



    /**
     * Validates single rule
     * @param    string     rule type
     * @param    array      optional extra rule data
     * @return   bool
     */
    public function validate($operation, $arg)
    {
        switch ($operation) {
            case NForm::EQUAL:
                if (is_object($arg)) // compare with another form item?
                    return get_class($arg) === $this->getClass()
                        ? ($this->value === $arg->value)
                        : FALSE;
                return $this->value === $arg;

            case NForm::FILLED:  // NULL, FALSE, '' ==> FALSE
                return (string) $this->value !== '';

            case NForm::SUBMITTED:
                return $this->isSubmitted();
        }
        return FALSE;
    }



    public function getClientScript($operation, $arg)
    {}


    /**
     * New rule or condition notification
     * @param    bool       rule or condition? TRUE means rule
     * @param    string     rule type
     * @param    array      optional extra rule data
     * @return   void
     */
    public function notifyRule($isRule, $operation, $arg)
    {
    }

}







/**
 * Text input form control
 */
class NFormTextItem extends NFormItem
{
    /** @var string */
    public $emptyValue = '';

    /** @var bool */
    public $autoTrim = TRUE;


    public function __construct(NComponent $parent, $name, $label=NULL, $secret=FALSE)
    {
        parent::__construct($parent, $name, $label);
        $this->control->type = $secret ? 'password' : 'text';
    }



    public function setValue($value)
    {
        $value = (string) $value;
        if ($this->autoTrim) $value = trim($value);
        $this->value = ($value === $this->emptyValue ? '' : $value);
        $this->updateControl();
    }



    public function setEmptyValue($value)
    {
        $this->emptyValue = $value;
        $this->updateControl();
    }



    protected function updateControl()
    {
        $this->control->value = $this->value === '' ? $this->emptyValue : $this->value;
    }



    public function notifyRule($isRule, $operation, $arg)
    {
        if ($isRule && $operation === NForm::LENGTH) $this->control->maxlength = $arg[1];
        elseif ($isRule && $operation === NForm::MAXLENGTH) $this->control->maxlength = $arg;
        elseif ($operation === NForm::REGEXP && $arg[0] !== '/') throw new NException('Invalid regexp');


        parent::notifyRule($isRule, $operation, $arg);
    }



    public function validate($operation, $arg)
    {
        switch ($operation) {
            // bug #33268 iconv_strlen works since PHP 5.0.5
            case NForm::MINLENGTH:
                return iconv_strlen($this->value) >= $arg;
            case NForm::MAXLENGTH:
                return iconv_strlen($this->value) <= $arg;
            case NForm::LENGTH:
                return iconv_strlen($this->value) >= $arg[0] && iconv_strlen($this->value) <= $arg[1];
            case NForm::EMAIL:
                return preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $this->value);
            case NForm::URL:
                return preg_match('/^.+\.[a-z]{2,6}(\\/.*)?$/i', $this->value);
            case NForm::REGEXP:
                return preg_match($arg, $this->value);
            case NForm::NUMERIC:
                return preg_match('/^-?[0-9]+$/', $this->value);
            case NForm::FLOAT:
                return preg_match('/^-?[0-9]*[.,]?[0-9]+$/', $this->value);
            case NForm::RANGE:
                return $this->value >= $arg[0] && $this->value <= $arg[1];
        }
        return parent::validate($operation, $arg);
    }



    public function getClientScript($operation, $arg)
    {
        // trim
        $tmp = "el = document.getElementById('" . $this->id . "');\n\t" .
               "var val = el.value.replace(/^\\s+/, '').replace(/\\s+\$/, '');\n\t";
        switch ($operation) {
            case NForm::EQUAL:
                if (is_object($arg)) // compare with another form item?
                    return get_class($arg) === $this->getClass()
                        ? $tmp . "res = val==document.getElementById('" . $arg->id . "').value;" // missing trim
                        : 'res = false;';

                return $tmp . "res = val=='" . addslashes($arg) . "';";

            case NForm::FILLED:
                return $tmp . "res = val!='' && val!='" . addslashes($this->emptyValue) . "';";
            case NForm::MINLENGTH:
                return $tmp . "res = val.length>=" . (int) $arg . ";";
            case NForm::MAXLENGTH:
                return $tmp . "res = val.length<=" . (int) $arg . ";";
            case NForm::LENGTH:
                return $tmp . "res = val.length>=" . (int) $arg[0] . " && val.length<=" . (int) $arg[1] . ";";
            case NForm::EMAIL:
                return $tmp . 'res = /^[^@]+@[^@]+\.[a-z]{2,6}$/i.test(val);';
            case NForm::URL:
                return $tmp . 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val);';
            case NForm::REGEXP:
                return $tmp . "res = $arg.test(val);";
            case NForm::NUMERIC:
                return $tmp . "res = /^-?[0-9]+$/.test(val);";
            case NForm::FLOAT:
                return $tmp . "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val);";
            case NForm::RANGE:
                return $tmp . "res = parseFloat(val)>=" . (int) $arg[0] . " && parseFloat(val)<=" . (int) $arg[1] . ";";
        }
        return FALSE;
    }

}







/**
 * Text input form control - text area
 */
class NFormTextAreaItem extends NFormTextItem
{
    /** @var bool */
    public $autoTrim = FALSE;


    public function __construct(NComponent $parent, $name, $label, $cols, $rows)
    {
        parent::__construct($parent, $name, $label);

        $this->control->setName('textarea');
        $this->control->type = NULL;
        $this->control->cols = $cols;
        $this->control->rows = $rows;
    }



    protected function updateControl()
    {
        $this->control->setText($this->value === '' ? $this->emptyValue : $this->value);
    }

}







/**
 * File upload form control
 */
class NFormFileItem extends NFormItem
{

    public function __construct(NComponent $parent, $name, $label)
    {
        parent::__construct($parent, $name, $label);
        $this->control->type = 'file';
    }



    public function setValue($value)
    {
        $this->value = is_array($value) && is_uploaded_file($value['tmp_name']) ? $value : NULL;
    }



    public function validate($operation, $arg)
    {
        switch ($operation) {
            case NForm::FILLED:
                return $this->isOK();
            case NForm::MAXFILESIZE:
                // TODO!
                return FALSE;
            case NForm::MIMETYPE:
                // TODO!
                return FALSE;
        }
        return parent::validate($operation, $arg);
    }



    public function isOK()
    {
        return $this->value && $this->value['error'] === UPLOAD_ERR_OK;
    }



    public function move($dest)
    {
        return move_uploaded_file($this->value['tmp_name'], $dest);
    }



    public function getImageSize()
    {
        return getimagesize($this->value['tmp_name']);
    }

}







/**
 * Hidden form control
 */
class NFormHiddenItem extends NFormItem
{

    public function __construct(NComponent $parent, $name)
    {
        parent::__construct($parent, $name);
        $this->control->id = NULL;
        $this->control->type = 'hidden';
        $this->label = NULL;
    }



    public function setValue($value)
    {
        $this->value = (string) $value;
        $this->control->value = $this->value;
    }


}







/**
 * Button form control
 */
class NFormButtonItem extends NFormItem
{
    /** @var NForm */
    protected $form;


    public function __construct(NComponent $parent, $name, $label, $isSubmit)
    {
        parent::__construct($parent, $name, $label);
        $this->control->id = NULL;
        $this->control->type = $isSubmit ? 'submit' : 'button';
        $this->control->value = $label;
        $this->label = NULL;
        $this->form = $parent;
    }



    public function setHttpValue($data)
    {
        $this->value = isset($data[$this->name]);
    }



    public function isSubmitted()
    {
        return $this->value;
    }



    public function notifyRule($isRule, $operation, $arg)
    {
        if ($operation === NForm::SUBMITTED || $operation === ~NForm::SUBMITTED) {
            $this->form->validateMode = NForm::VM_BUTTON;

            if ($this->control->type === 'submit' || $this->control->type === 'image') {
                $this->control->onclick .= 'return ' . $this->form->validateFunction . "(this);";
            }
        }

        parent::notifyRule($isRule, $operation, $arg);
    }



    public function getClientScript($operation, $arg)
    {
        if ($operation === NForm::SUBMITTED) {
            return "el=null; res=sender && sender.name=='$this->name';";
        }
        return FALSE;
    }

}







class NFormImageItem extends NFormButtonItem
{

    public function __construct(NComponent $parent, $name, $src, $alt)
    {
        parent::__construct($parent, $name, NULL, TRUE);
        $this->control->type = 'image';
        $this->control->src = $src;
        $this->control->alt = $alt;
    }



    public function setHttpValue($data)
    {
        $this->value = isset($data[$this->name . '_x']);
    }

}







/**
 * Check box form control
 */
class NFormCheckboxItem extends NFormItem
{

    public function __construct(NComponent $parent, $name, $label)
    {
        parent::__construct($parent, $name, $label);
        $this->control->type = 'checkbox';
    }



    public function setValue($value)
    {
        $this->value = (bool) $value;
        $this->control->checked = $this->value;
    }



    public function getClientScript($operation, $arg)
    {
        if ($operation === NForm::EQUAL) {
            return "el = document.getElementById('" . $this->id . "');\n\tres = " . ($arg ? '' : '!') . "el.checked;";
        }
        return FALSE;
    }

}







/**
 * Set of radio buttons form control
 */
class NFormRadioItem extends NFormItem
{
    /** @var array */
    protected $items;



    public function __construct(NComponent $parent, $name, $label, $items)
    {
        if (!is_array($items))
            throw new NException('Items must be array.');

        parent::__construct($parent, $name, $label);

        $this->label->for = NULL;
        $el = $this->control = NHtml::el();

        $counter = 0;
        foreach ($items as $key => $val) {
            $id = $this->id . '-' . $counter;
            $counter++;

            $this->items[$key] = $el->create('input', array(
                'type' => 'radio',
                'id' => $id,
                'name' => $name,
                'value' => $key,
            ));

            $el->create('label', $val)->for($id);

            $el->create('br');
        }
    }



    public function setValue($value)
    {
        if ($this->value !== NULL) {
            $this->items[$value]->checked = FALSE;
        }

        if (isset($this->items[$value])) {
            $this->value = $value;
            $this->items[$value]->checked = TRUE;

        } else {
            $this->value = NULL;
        }
    }



    public function setEvent($event)
    {
        foreach ($this->items as $el) {
            $el->onclick = $event;
        }
    }



    public function validate($operation, $arg)
    {
        if ($operation === NForm::FILLED) {
            return $this->value !== NULL;
        }
        return parent::validate($operation, $arg);
    }



    public function getClientScript($operation, $arg)
    {
        switch ($operation) {
            case NForm::EQUAL:
                return "res = false;\n\t" .
                    "for (var i=0;i<" . count($this->items) . ";i++) {\n\t\t" .
                    "el = document.getElementById('" . $this->id . "-'+i);\n\t\t" .
                    "if (el.checked && el.value=='" . addslashes($arg) . "') { res = true; break; }\n\t" .
                    "}\n\tel = null;";
            case NForm::FILLED:
                return "res = false; el=null;\n\t" .
                    "for (var i=0;i<" . count($this->items) . ";i++) " .
                    "if (document.getElementById('" . $this->id . "-'+i).checked) { res = true; break; }";
        }
        return FALSE;
    }

}







/**
 * Select box form control
 */
class NFormSelectItem extends NFormItem
{
    /** @var array */
    protected $items;

    /** @var bool */
    public $skipFirst = FALSE;


    public function __construct(NComponent $parent, $name, $label, $items, $multiple)
    {
        if (!is_array($items))
            throw new NException('Items must be array.');

        parent::__construct($parent, $name, $label);

        $el = $this->control;
        $el->setName('select');
        $el->multiple = (bool) $multiple;
        $el->onmousewheel = 'return false';  // prevent accidental change
        if ($multiple) $el->name .= '[]';

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $opt = $el->create('optgroup')->label($key);
                foreach ($value as $key2 => $value2) {
                    $this->items[$key2] = $opt->create('option', $value2)->value($key2);
                }
            } else {
                $this->items[$key] = $el->create('option', $value)->value($key);
            }
        }

        $this->label->onclick = 'return false';  // prevent "deselect" for IE 5 - 6

    }



    public function setValue($value)
    {
        $allowed = $this->items;
        if ($this->skipFirst) array_shift($allowed);

        foreach ($this->items as $el) {
            $el->selected = FALSE;
        }

        if ($this->control->multiple) {
            $this->value = array();
            foreach ((array) $value as $val) {
                if (isset($allowed[$val])) {
                    $this->value[] = $val;
                    $allowed[$val]->selected = TRUE;
                }
            }

        } else {
            if (isset($allowed[$value])) {
                $this->value = $value;
                $allowed[$value]->selected = TRUE;
            } else {
                $this->value = NULL;
            }
        }
    }



    public function setEvent($event)
    {
        $this->control->onchange = $event;
    }



    public function validate($operation, $arg)
    {
        if ($operation === NForm::FILLED) {
            return $this->control->multiple ? (bool) $this->value : $this->value !== NULL;
        }
        return parent::validate($operation, $arg);
    }



    public function getClientScript($operation, $arg)
    {
        $tmp = "el = document.getElementById('" . $this->id . "');\n\t";
        $first = $this->skipFirst ? 1 : 0;
        switch ($operation) {
            case NForm::EQUAL:
                $arg = addslashes($arg);
                return $tmp . "res = false;\n\t" .
                    "for (var i=$first;i<el.options.length;i++)\n\t\t" .
                    "if (el.options[i].selected && el.options[i].value=='$arg') { res = true; break; }";
            case NForm::FILLED:
                return $tmp . "res = el.selectedIndex >= $first;";
        }
        return FALSE;
    }

}
