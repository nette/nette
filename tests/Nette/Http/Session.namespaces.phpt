<?php

/**
 * Test: Nette\Http\Session namespaces.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session,
	Nette\Http\SessionNamespace;



require __DIR__ . '/../bootstrap.php';



ob_start();

$session = Nette\Environment::getSession();
Assert::false( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' );

$namespace = $session->getNamespace('trees');
Assert::false( $session->hasNamespace('trees'), 'hasNamespace() should have returned FALSE for a namespace with no keys set' );

$namespace->hello = 'world';
Assert::true( $session->hasNamespace('trees'), 'hasNamespace() should have returned TRUE for a namespace with keys set' );

$namespace = $session->getNamespace('default');
Assert::true( $namespace instanceof SessionNamespace );

try {
	$namespace = $session->getNamespace('');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', 'Session namespace must be a non-empty string.', $e );
}
