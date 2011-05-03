<?php

/**
 * Test: Nette\Http\SessionNamespace remove.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$namespace = $session->getNamespace('three');
$namespace->a = 'apple';
$namespace->p = 'papaya';
$namespace['c'] = 'cherry';

$namespace = $session->getNamespace('three');
Assert::same( 'a=apple&p=papaya&c=cherry', http_build_query($namespace->getIterator()) );


// removing
$namespace->remove();
Assert::same( '', http_build_query($namespace->getIterator()) );
