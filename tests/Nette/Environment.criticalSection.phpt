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



require __DIR__ . '/../initialize.php';



$key = '../' . implode('', range("\x00", "\x1F"));

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
Environment::setVariable('tempDir', TEMP_DIR);
T::purge(TEMP_DIR);


T::note('entering');
Environment::enterCriticalSection($key);

T::note('leaving');
Environment::leaveCriticalSection($key);

try {
	T::note('leaving not entered');
	Environment::leaveCriticalSection('notEntered');
} catch (Exception $e) {
	T::dump( $e );
}


__halt_compiler() ?>

------EXPECT------
entering

leaving

leaving not entered

Exception InvalidStateException: Critical section has not been initialized.
