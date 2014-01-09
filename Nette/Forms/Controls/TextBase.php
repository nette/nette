<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette,
	Nette\Forms\Form;


/**
 * Implements the basic functionality common to text input controls.
 *
 * @author     David Grudl
 *
 * @property   string $emptyValue
 */
abstract class TextBase extends BaseControl
{
	/** @var string */
	protected $emptyValue = '';

	/** @var array */
	protected $filters = array();

	/** @var mixed unfiltered submitted value */
	protected $rawValue = '';


	/**
	 * Sets control's value.
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value === NULL) {
			$value = '';
		} elseif (!is_scalar($value) && !method_exists($value, '__toString')) {
			throw new Nette\InvalidArgumentException('Value must be scalar or NULL, ' . gettype($value) . " given in field '{$this->name}'.");
		}
		$this->rawValue = $this->value = $value;
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return string
	 */
	public function getValue()
	{
		$value = $this->value;
		if (!empty($this->control->maxlength)) {
			$value = Nette\Utils\Strings::substring($value, 0, $this->control->maxlength);
		}
		foreach ($this->filters as $filter) {
			$value = (string) call_user_func($filter, $value);
		}
		return $value === $this->translate($this->emptyValue) ? '' : $value;
	}


	/**
	 * Sets the special value which is treated as empty string.
	 * @param  string
	 * @return self
	 */
	public function setEmptyValue($value)
	{
		$this->emptyValue = (string) $value;
		return $this;
	}


	/**
	 * Returns the special value which is treated as empty string.
	 * @return string
	 */
	public function getEmptyValue()
	{
		return $this->emptyValue;
	}


	/**
	 * Appends input string filter callback.
	 * @param  callable
	 * @return self
	 */
	public function addFilter($filter)
	{
		$this->filters[] = Nette\Utils\Callback::check($filter);
		return $this;
	}


	public function getControl()
	{
		$el = parent::getControl();
		if ($this->emptyValue !== '') {
			$el->attrs['data-nette-empty-value'] = $this->translate($this->emptyValue);
		}
		if (isset($el->placeholder)) {
			$el->placeholder = $this->translate($el->placeholder);
		}
		return $el;
	}


	public function addRule($validator, $message = NULL, $arg = NULL)
	{
		if ($validator === Form::LENGTH || $validator === Form::MAX_LENGTH) {
			$tmp = is_array($arg) ? $arg[1] : $arg;
			$this->control->maxlength = is_scalar($tmp) ? $tmp : NULL;
		}
		return parent::addRule($validator, $message, $arg);
	}

}
