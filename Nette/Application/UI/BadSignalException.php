<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Signal exception.
 *
 * @author     David Grudl
 */
class BadSignalException extends Nette\Application\BadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
