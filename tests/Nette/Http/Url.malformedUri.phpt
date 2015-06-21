<?php

/**
 * Test: Nette\Http\Url malformed URI.
 */

use Nette\Http\Url;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$url = new Url('http:///');
}, 'InvalidArgumentException', "Malformed or unsupported URI 'http:///'.");
