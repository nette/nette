<?php

/**
 * Test: Nette\Utils\Strings::normalize()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Strings;



require __DIR__ . '/../bootstrap.php';



Assert::same( "Hello\n  World",  Strings::normalize("\r\nHello  \r  World \n\n") );

Assert::same( "HelloWorld",  Strings::normalize("Hello\xC2\xA0World") );

Assert::same( "Hello World",  Strings::normalize("Hello\xC2\x83World") );

Assert::same( "Hello\nWorld",  Strings::normalize("Hello\xC2\x84World") );
