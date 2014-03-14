<?php

/**
 * Test: Nette\Security\Passwords::verify()
 *
 * @author     David Grudl
 * @phpversion 5.3.7
 */

use Nette\Security\Passwords,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::true(Passwords::verify('dg', '$2y$05$123456789012345678901uTj3G.8OMqoqrOMca1z/iBLqLNaWe6DK'));
Assert::false(Passwords::verify('dg', '$2x$05$123456789012345678901uTj3G.8OMqoqrOMca1z/iBLqLNaWe6DK'));
Assert::false(Passwords::verify('dgx', '$2y$05$123456789012345678901uTj3G.8OMqoqrOMca1z/iBLqLNaWe6DK'));
