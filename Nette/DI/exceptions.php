<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * Service not found exception.
 */
class MissingServiceException extends Nette\InvalidStateException
{
}


/**
 * Service creation exception.
 */
class ServiceCreationException extends Nette\InvalidStateException
{
}
