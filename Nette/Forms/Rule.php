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



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Single validation rule or condition represented as value object.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
final class Rule extends /*Nette::*/Object
{
	/** @var IFormControl */
	public $control;

	/** @var mixed */
	public $operation;

	/** @var mixed */
	public $arg;

	/** @var bool */
	public $isCondition = FALSE;

	/** @var bool */
	public $isNegative = FALSE;

	/** @var string */
	public $message;

	/** @var Rules */
	public $subRules;

}
