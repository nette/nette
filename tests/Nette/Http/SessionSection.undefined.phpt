<?php

/**
 * Test: Nette\Http\SessionSection undefined property.
 */

use Nette\Http\Session,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$session = new Session(new Nette\Http\Request(new Nette\Http\UrlScript), new Nette\Http\Response);

$namespace = $session->getSection('one');
Assert::false( isset($namespace->undefined) );
Assert::null( $namespace->undefined ); // Getting value of non-existent key
Assert::same( '', http_build_query($namespace->getIterator()) );
