<?php

/**
 * Test: Nette\Web\SessionNamespace separated space.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Session;



require __DIR__ . '/../initialize.php';



$session = new Session;
$namespace1 = $session->getNamespace('namespace1');
$namespace1b = $session->getNamespace('namespace1');
$namespace2 = $session->getNamespace('namespace2');
$namespace2b = $session->getNamespace('namespace2');
$namespace3 = $session->getNamespace('default');
$namespace3b = $session->getNamespace('default');
$namespace1->a = 'apple';
$namespace2->a = 'pear';
$namespace3->a = 'orange';
T::dump( $namespace1->a !== $namespace2->a && $namespace1->a !== $namespace3->a && $namespace2->a !== $namespace3->a ); // Test session improperly shared namespaces
T::dump( $namespace1->a === $namespace1b->a );
T::dump( $namespace2->a === $namespace2b->a );
T::dump( $namespace3->a === $namespace3b->a );



__halt_compiler() ?>

------EXPECT------
TRUE

TRUE

TRUE

TRUE
