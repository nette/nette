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
dump( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' ); // False

$namespace = $session->getNamespace('trees');
dump( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' ); // False

$namespace->hello = 'world';
dump( $session->hasNamespace('trees'), 'hasNamespace() should have returned TRUE for a namespace with keys set' ); // True

$namespace = $session->getNamespace('default');
dump( $namespace instanceof /*Nette\Web\*/SessionNamespace ); // TRUE

try {
	$namespace = $session->getNamespace('');
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
hasNamespace() should have returned FALSE for a namespace with no keys set: bool(FALSE)

hasNamespace() should have returned FALSE for a namespace with no keys set: bool(FALSE)

hasNamespace() should have returned TRUE for a namespace with keys set: bool(TRUE)

bool(TRUE)

Exception InvalidArgumentException: Session namespace must be a non-empty string.
