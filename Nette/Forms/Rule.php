<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * Single validation rule or condition represented as value object.
 *
 * @author     David Grudl
 */
class Rule extends Nette\Object
{
	/** type */
	const CONDITION = 1;

	/** type */
	const VALIDATOR = 2;

	/** @var IControl */
	public $control;

	/** @var mixed */
	public $operation;

	/** @var mixed */
	public $arg;

	/** @var int (CONDITION, VALIDATOR, FILTER) */
	public $type;

	/** @var bool */
	public $isNegative = FALSE;

	/** @var string (only for VALIDATOR type) */
	public $message;

	/** @var Rules (only for CONDITION type)  */
	public $subRules;

}
