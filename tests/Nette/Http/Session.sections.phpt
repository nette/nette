<?php

/**
 * Test: Nette\Http\Session sections.
 */

use Nette\Http\Session;
use Nette\Http\SessionSection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


ob_start();

$session = new Session(new Nette\Http\Request(new Nette\Http\UrlScript), new Nette\Http\Response);

Assert::false($session->hasSection('trees')); // hasSection() should have returned FALSE for a section with no keys set

$section = $session->getSection('trees');
Assert::false($session->hasSection('trees')); // hasSection() should have returned FALSE for a section with no keys set

$section->hello = 'world';
Assert::true($session->hasSection('trees')); // hasSection() should have returned TRUE for a section with keys set

$section = $session->getSection('default');
Assert::type('Nette\Http\SessionSection', $section);
