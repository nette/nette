<?php

/**
 * Test: Nette\Loaders\NetteLoader basic usage.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../../../vendor/nette/tester/Tester/bootstrap.php';
require __DIR__ . '/../../../Nette/Loaders/NetteLoader.php';


Assert::false( class_exists('Nette\Framework') );

Nette\Loaders\NetteLoader::getInstance()->register();

Assert::true( class_exists('Nette\Framework') );


require __DIR__ . '/../bootstrap.php';

Assert::error(function() {
	class_exists('Nette\Http\User');
}, E_USER_WARNING, 'Class Nette\Http\User has been renamed to Nette\Security\User.');
