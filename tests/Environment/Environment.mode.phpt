<?php

/**
 * Test: Nette\Environment modes.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



T::dump( Environment::isConsole(), "Is console?" );

T::dump( Environment::isProduction(), "Is production mode?" );

T::note("Setting my mode...");
Environment::setMode('myMode', 123);

T::dump( Environment::getMode('myMode'), "Is enabled?" );



__halt_compiler() ?>

------EXPECT------
Is console? bool(FALSE)

Is production mode? bool(TRUE)

Setting my mode...

Is enabled? bool(TRUE)
