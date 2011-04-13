<?php

/**
 * Test: Nette\Environment name.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



//define('ENVIRONMENT', 'lab');

Assert::same( 'production', Environment::getName(), 'Name:' );



try {
	// Setting name:
	Environment::setName('lab2');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', 'Environment name has already been set.', $e );
}
