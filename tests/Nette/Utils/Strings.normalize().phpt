<?php

/**
 * Test: Nette\Utils\Strings::normalize()
 *
 * @author     David Grudl
 */

use Nette\Utils\Strings,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( "Hello\n  World",  Strings::normalize("\r\nHello  \r  World \n\n") );

Assert::same( "Hello  World",  Strings::normalize("Hello \x00 World") );
Assert::same( "Hello  World",  Strings::normalize("Hello \x0B World") );
Assert::same( "Hello  World",  Strings::normalize("Hello \x1F World") );
Assert::same( "Hello  World",  Strings::normalize("Hello \x7F World") );
