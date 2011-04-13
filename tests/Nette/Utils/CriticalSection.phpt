<?php

/**
 * Test: Nette\Utils\CriticalSection.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\CriticalSection;



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
	Assert::exception('Nette\InvalidStateException', 'Critical section has not been initialized.', $e );
}

try {
	// doubled entering
	CriticalSection::enter();
	CriticalSection::enter();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', 'Critical section has already been entered.', $e );
}
