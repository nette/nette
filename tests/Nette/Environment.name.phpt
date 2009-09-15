<?php

/**
 * Test: Environment configuration.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



//define('ENVIRONMENT', 'lab');

dump( Environment::getName(), "Name:" );


try {
	message("Setting name:");
	Environment::setName('lab2');
	dump( Environment::getName() );

} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Name: string(10) "production"

Setting name:

Exception InvalidStateException: Environment name has been already set.
