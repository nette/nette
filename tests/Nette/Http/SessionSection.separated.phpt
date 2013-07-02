<?php

/**
 * Test: Nette\Http\SessionSection separated space.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Session;


require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', TEMP_DIR);


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

$namespace1 = $session->getSection('namespace1');
$namespace1b = $session->getSection('namespace1');
$namespace2 = $session->getSection('namespace2');
$namespace2b = $session->getSection('namespace2');
$namespace3 = $session->getSection('default');
$namespace3b = $session->getSection('default');
$namespace1->a = 'apple';
$namespace2->a = 'pear';
$namespace3->a = 'orange';
Assert::true( $namespace1->a !== $namespace2->a && $namespace1->a !== $namespace3->a && $namespace2->a !== $namespace3->a );
Assert::same( $namespace1->a, $namespace1b->a );
Assert::same( $namespace2->a, $namespace2b->a );
Assert::same( $namespace3->a, $namespace3b->a );
