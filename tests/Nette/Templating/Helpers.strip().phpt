<?php

/**
 * Test: Nette\Templating\Helpers::strip()
 */

use Nette\Templating\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( '', Helpers::strip('') );

Assert::same( '', Helpers::strip("\r\n ") );

Assert::same( '<p> Hello </p>', Helpers::strip("<p> Hello </p>\r\n ") );
