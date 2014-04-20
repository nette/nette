<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Hidden form control used to store a non-displayed value.
 *
 * @author     David Grudl
 */
class HiddenField extends BaseControl
{
	/** @var bool */
	private $persistValue;


	public function __construct($persistentValue = NULL)
	{
		parent::__construct();
		$this->control->type = 'hidden';
		if ($persistentValue !== NULL) {
			$this->unmonitor('Nette\Forms\Form');
			$this->persistValue = TRUE;
			$this->value = (string) $persistentValue;
		}
	}


	/**
	 * Sets control's value.
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== NULL && !method_exists($value, '__toString')) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or NULL, %s given in field '%s'.", gettype($value), $this->name));
		}
		if (!$this->persistValue) {
			$this->value = (string) $value;
		}
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$el = clone $this->control;
		return $el->addAttributes(array(
			'name' => $this->getHtmlName(),
			'id' => $this->getHtmlId(),
			'disabled' => $this->isDisabled(),
			'value' => $this->value,
		));
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		return NULL;
	}


	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		$this->getForm()->addError($message);
	}

}
