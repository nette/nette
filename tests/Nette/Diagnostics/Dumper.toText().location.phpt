<?php

/**
 * Test: Nette\Diagnostics\Dumper::toText() with location
 */

use Nette\Diagnostics\Dumper,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::match( '"Hello" (5)
in %a%:%d%
', Dumper::toText( trim(" Hello "), array("location" => TRUE) ) );
