<?php

/**
 * Test: Nette\Web\Session namespaces.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Session;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



ob_start();

$session = new Session;
Assert::false( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' );

$namespace = $session->getNamespace('trees');
Assert::false( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' );

$namespace->hello = 'world';
Assert::true( $session->hasNamespace('trees'), 'hasNamespace() should have returned TRUE for a namespace with keys set' );

$namespace = $session->getNamespace('default');
Assert::true( $namespace instanceof /*Nette\Web\*/SessionNamespace );

try {
	$namespace = $session->getNamespace('');
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidArgumentException: Session namespace must be a non-empty string.
