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

Assert::throws(function() {
	// leaving not entered
	CriticalSection::leave();
}, 'Nette\InvalidStateException', 'Critical section has not been initialized.');

Assert::throws(function() {
	// doubled entering
	CriticalSection::enter();
	CriticalSection::enter();
}, 'Nette\InvalidStateException', 'Critical section has already been entered.');
