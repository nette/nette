<?php

/**
 * Test: Nette\Environment critical sections.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../NetteTest/initialize.php';



$key = '../' . implode('', range("\x00", "\x1F"));

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
Environment::setVariable('tempDir', TEMP_DIR);
NetteTestHelpers::purge(TEMP_DIR);


output('entering');
Environment::enterCriticalSection($key);

output('leaving');
Environment::leaveCriticalSection($key);

try {
	output('leaving not entered');
	Environment::leaveCriticalSection('notEntered');
} catch (Exception $e) {
	dump( $e );
}


__halt_compiler() ?>

------EXPECT------
entering

leaving

leaving not entered

Exception InvalidStateException: Critical section has not been initialized.
