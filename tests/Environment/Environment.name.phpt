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

Assert::same( 'production', Environment::getName(), 'Name:' );



try {
	// Setting name:
	Environment::setName('lab2');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Environment name has been already set.', $e );
}
