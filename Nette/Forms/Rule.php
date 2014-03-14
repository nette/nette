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
	/** @var IControl */
	public $control;

	/** @var mixed */
	public $validator;

	/** @var mixed */
	public $arg;

	/** @var bool */
	public $isNegative = FALSE;

	/** @var string */
	public $message;

	/** @var Rules  for conditions */
	public $branch;

}
