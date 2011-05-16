<?php

/**
 * Test: Nette\Environment modes.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Assert::false( Environment::isConsole(), 'Is console?' );

Assert::true( Environment::isProduction(), 'Is production mode?' );
