<?php

/**
 * Test: Nette\Http\Session sections.
 *
 * @author     David Grudl
 */

use Nette\Http\Session,
	Nette\Http\SessionSection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


ob_start();

$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

Assert::false( $session->hasSection('trees') ); // hasSection() should have returned FALSE for a section with no keys set

$section = $session->getSection('trees');
Assert::false( $session->hasSection('trees') ); // hasSection() should have returned FALSE for a section with no keys set

$section->hello = 'world';
Assert::true( $session->hasSection('trees') ); // hasSection() should have returned TRUE for a section with keys set

$section = $session->getSection('default');
Assert::type( 'Nette\Http\SessionSection', $section );
