<?php

/**
 * Test: Nette\Utils\Strings::random()
 *
 * @author     David Grudl
 */

use Nette\Utils\Strings,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( 10, strlen(Strings::random()) );
Assert::same( 5, strlen(Strings::random(5)) );
Assert::same( 200, strlen(Strings::random(200)) );

Assert::truthy( preg_match('#^[0-9a-z]+$#', Strings::random()) );
Assert::truthy( preg_match('#^[0-9]+$#', Strings::random(1000, '0-9')) );
Assert::truthy( preg_match('#^[0a-z12]+$#', Strings::random(1000, '0a-z12')) );
Assert::truthy( preg_match('#^[-a]+$#', Strings::random(1000, '-a')) );
Assert::truthy( preg_match('#^[0]+$#', Strings::random(1000, '000')) );
