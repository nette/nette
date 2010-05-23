<?php

/**
 * Test: Nette\Environment modes.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



dump( Environment::isConsole(), "Is console?" );

dump( Environment::isProduction(), "Is production mode?" );

output("Setting my mode...");
Environment::setMode('myMode', 123);

dump( Environment::getMode('myMode'), "Is enabled?" );



__halt_compiler() ?>

------EXPECT------
Is console? bool(FALSE)

Is production mode? bool(TRUE)

Setting my mode...

Is enabled? bool(TRUE)
