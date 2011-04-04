<?php

/**
 * Test: Nette\CriticalSection.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\CriticalSection;



require __DIR__ . '/../bootstrap.php';



// entering
CriticalSection::enter();

// leaving
CriticalSection::leave();

try {
	// leaving not entered
	CriticalSection::leave();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Critical section has not been initialized.', $e );
}

try {
	// doubled entering
	CriticalSection::enter();
	CriticalSection::enter();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Critical section has already been entered.', $e );
}
