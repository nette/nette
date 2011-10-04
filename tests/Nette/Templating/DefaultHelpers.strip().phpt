<?php

/**
 * Test: Nette\Templating\DefaultHelpers::strip()
 *
 * @author     David Grudl
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Templating\DefaultHelpers;



require __DIR__ . '/../bootstrap.php';



Assert::same( '', DefaultHelpers::strip('') );

Assert::same( '', DefaultHelpers::strip("\r\n ") );

Assert::same( '<p> Hello </p>', DefaultHelpers::strip("<p> Hello </p>\r\n ") );
