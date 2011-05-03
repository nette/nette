<?php

/**
 * Test: Nette\Http\SessionNamespace separated space.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$namespace1 = $session->getNamespace('namespace1');
$namespace1b = $session->getNamespace('namespace1');
$namespace2 = $session->getNamespace('namespace2');
$namespace2b = $session->getNamespace('namespace2');
$namespace3 = $session->getNamespace('default');
$namespace3b = $session->getNamespace('default');
$namespace1->a = 'apple';
$namespace2->a = 'pear';
$namespace3->a = 'orange';
Assert::true( $namespace1->a !== $namespace2->a && $namespace1->a !== $namespace3->a && $namespace2->a !== $namespace3->a );
Assert::true( $namespace1->a === $namespace1b->a );
Assert::true( $namespace2->a === $namespace2b->a );
Assert::true( $namespace3->a === $namespace3b->a );
