<?php

/**
 * Test: Nette\Security\Passwords::needsRehash()
 *
 * @author     David Grudl
 * @phpversion 5.3.7
 */

use Nette\Security\Passwords,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::true(Passwords::needsRehash('$2y$05$123456789012345678901uTj3G.8OMqoqrOMca1z/iBLqLNaWe6DK'));
Assert::false(Passwords::needsRehash('$2y$05$123456789012345678901uTj3G.8OMqoqrOMca1z/iBLqLNaWe6DK', array('cost' => 5)));
