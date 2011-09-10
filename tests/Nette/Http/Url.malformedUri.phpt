<?php

/**
 * Test: Nette\Http\Url malformed URI.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Url;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$url = new Url('http:///');
}, 'InvalidArgumentException', "Malformed or unsupported URI 'http:///'.");
