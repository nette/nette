<?php

/**
 * Test: Nette\Security\Identity.
 */

use Nette\Security\Identity,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$id = new Identity(12, 'admin', array('name' => 'John'));

	Assert::same(12, $id->getId());
	Assert::same(12, $id->id);
	Assert::same(array('admin'), $id->getRoles());
	Assert::same(array('admin'), $id->roles);
	Assert::same(array('name' => 'John'), $id->getData());
	Assert::same('John', $id->name);
});


test(function() {
	$id = new Identity('12');
	Assert::same(12, $id->getId());


	$id = new Identity('12345678901234567890');
	Assert::same('12345678901234567890', $id->getId());
});
