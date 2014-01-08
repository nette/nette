<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * @author Petr Morávek <petr@pada.cz>
 */
class RangeControl extends TextInput
{

	/**
	 * @param  string  label
	 * @param  array  allowed number range
	 * @param  string  error message for numeric validation rule
	 * @param  string  error message for range validation rule
	 */
	public function __construct($label = NULL, array $range = array(0, 100), $numberErrorMessage = NULL, $rangeErrorMessage = NULL)
	{
		parent::__construct($label);
		$this->setType('range');
		$this->setRequired();
		$this->addRule(Nette\Forms\Form::NUMERIC, $numberErrorMessage);
		$this->addRule(Nette\Forms\Form::RANGE, $rangeErrorMessage, $range);
		//TODO filter for rounding the value to match the step attribute
	}

	/**
	 * @param string
	 * @return void
	 * @throws Nette\NotSupportedException range control must be always filled
	 */
	public function setEmptyValue($value)
	{
		throw new Nette\NotSupportedException("Range control must be always filled.");
	}

}
