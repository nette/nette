<?php

/**
 * Test: Nette\Http\Url malformed URI.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$url = new Url('http:///');
}, 'InvalidArgumentException', "Malformed or unsupported URI 'http:///'.");
