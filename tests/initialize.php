<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */

require __DIR__ . '/NetteTest/TestHelpers.php';
require __DIR__ . '/NetteTest/Assert.php';
require __DIR__ . '/../Nette/loader.php';

TestHelpers::startup();

if (function_exists('class_alias')) {
	class_alias('TestHelpers', 'T');
} else {
	class T extends TestHelpers {}
}