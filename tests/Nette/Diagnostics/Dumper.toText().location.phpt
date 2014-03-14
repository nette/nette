<?php

/**
 * Test: Nette\Diagnostics\Dumper::toText() with location
 *
 * @author     David Grudl
 */

use Nette\Diagnostics\Dumper,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::match( '"Hello" (5)
in ' . __FILE__ . ':%d%
', Dumper::toText( trim(" Hello "), array("location" => TRUE) ) );
