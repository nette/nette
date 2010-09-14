<?php

/**
 * Test: Nette\Environment modes.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



Assert::false( Environment::isConsole(), 'Is console?' );


Assert::true( Environment::isProduction(), 'Is production mode?' );


// Setting my mode...
Environment::setMode('myMode', 123);

Assert::true( Environment::getMode('myMode'), 'Is enabled?' );
