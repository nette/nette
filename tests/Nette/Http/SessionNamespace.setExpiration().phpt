<?php

/**
 * Test: Nette\Http\SessionNamespace::setExpiration()
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



ob_start();

$session = Nette\Environment::getSession();

// try to expire whole namespace
$namespace = $session->getNamespace('expire');
$namespace->a = 'apple';
$namespace->p = 'pear';
$namespace['o'] = 'orange';
$namespace->setExpiration('+ 2 seconds');

$session->close();
sleep(3);
$session->start();

$namespace = $session->getNamespace('expire');
Assert::same( '', http_build_query($namespace->getIterator()) );


// try to expire only 1 of the keys
$namespace = $session->getNamespace('expireSingle');
$namespace->setExpiration(2, 'g');
$namespace->g = 'guava';
$namespace->p = 'plum';

$session->close();
sleep(3);
$session->start();

$namespace = $session->getNamespace('expireSingle');
Assert::same( 'p=plum', http_build_query($namespace->getIterator()) );
