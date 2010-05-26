<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */

namespace Nette\Application;

use Nette;



/**
 * Forbidden request exception - access denied.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class ForbiddenRequestException extends BadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
