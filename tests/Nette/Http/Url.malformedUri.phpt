<?php

/**
 * Test: Nette\Http\Url malformed URI.
 *
 * @author     David Grudl
 */

use Nette\Http\Url,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$url = new Url('http:///');
}, 'InvalidArgumentException', "Malformed or unsupported URI 'http:///'.");
