<?php

/**
 * Test: Nette\Web\SessionNamespace undefined property.
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Session;



require __DIR__ . '/../initialize.php';



$session = new Session;
$namespace = $session->getNamespace('one');
Assert::false( isset($namespace->undefined) );
Assert::null( $namespace->undefined, 'Getting value of non-existent key' );
Assert::same( '', http_build_query($namespace->getIterator()) );
