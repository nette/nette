<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Reflection;

use PDOException;


/**
 * Reference not found exception.
 */
class MissingReferenceException extends PDOException
{
}


/**
 * Ambiguous reference key exception.
 */
class AmbiguousReferenceKeyException extends PDOException
{
}
