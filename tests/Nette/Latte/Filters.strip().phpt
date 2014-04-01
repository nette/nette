<?php

/**
 * Test: Latte\Runtime\Filters::strip()
 *
 * @author     David Grudl
 */

use Latte\Runtime\Filters,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( '', Filters::strip('') );

Assert::same( '', Filters::strip("\r\n ") );

Assert::same( '<p> Hello </p>', Filters::strip("<p> Hello </p>\r\n ") );
