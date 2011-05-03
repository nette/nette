<?php

/**
 * Test: Nette\Http\SessionNamespace undefined property.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$namespace = $session->getNamespace('one');
Assert::false( isset($namespace->undefined) );
Assert::null( $namespace->undefined, 'Getting value of non-existent key' );
Assert::same( '', http_build_query($namespace->getIterator()) );
