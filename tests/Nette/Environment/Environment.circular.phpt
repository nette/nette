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
	Environment::loadConfig('config.circular.ini', 'production');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.', $e );
}
