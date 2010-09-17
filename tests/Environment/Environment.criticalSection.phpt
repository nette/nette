<?php

/**
 * Test: Nette\Environment critical sections.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



$key = '../' . implode('', range("\x00", "\x1F"));

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
Environment::setVariable('tempDir', TEMP_DIR);
TestHelpers::purge(TEMP_DIR);


// entering
Environment::enterCriticalSection($key);

// leaving
Environment::leaveCriticalSection($key);

try {
	// leaving not entered
	Environment::leaveCriticalSection('notEntered');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Critical section has not been initialized.', $e );
}
