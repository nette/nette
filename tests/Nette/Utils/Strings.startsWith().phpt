<?php

/**
 * Test: Nette\Utils\Strings::startsWith()
 *
 * @author     David Grudl
 */

use Nette\Utils\Strings,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::true( Strings::startsWith('123', NULL), "startsWith('123', NULL)" );
Assert::true( Strings::startsWith('123', ''), "startsWith('123', '')" );
Assert::true( Strings::startsWith('123', '1'), "startsWith('123', '1')" );
Assert::false( Strings::startsWith('123', '2'), "startsWith('123', '2')" );
Assert::true( Strings::startsWith('123', '123'), "startsWith('123', '123')" );
Assert::false( Strings::startsWith('123', '1234'), "startsWith('123', '1234')" );
