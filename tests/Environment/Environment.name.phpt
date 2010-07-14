<?php

/**
 * Test: Nette\Environment name.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



//define('ENVIRONMENT', 'lab');

T::dump( Environment::getName(), "Name:" );


try {
	T::note("Setting name:");
	Environment::setName('lab2');
	T::dump( Environment::getName() );

} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Name: "production"

Setting name:

Exception InvalidStateException: Environment name has been already set.
