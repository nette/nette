<?php

/**
 * Test: Nette\Environment circular references.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



try {
	Environment::setName(Environment::PRODUCTION);
	Environment::loadConfig('config.circular.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', 'Circular reference detected for variables: bar, foo, foobar.', $e );
}
