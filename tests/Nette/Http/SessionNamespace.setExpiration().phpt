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
$session->setExpiration('+10 seconds');

// try to expire whole namespace
$namespace = $session->getNamespace('expire');
$namespace->a = 'apple';
$namespace->p = 'pear';
$namespace['o'] = 'orange';
$namespace->setExpiration('+ 1 seconds');

$session->close();
sleep(2);
$session->start();

$namespace = $session->getNamespace('expire');
Assert::same( '', http_build_query($namespace->getIterator()) );


// try to expire only 1 of the keys
$namespace = $session->getNamespace('expireSingle');
$namespace->setExpiration(1, 'g');
$namespace->g = 'guava';
$namespace->p = 'plum';

$session->close();
sleep(2);
$session->start();

$namespace = $session->getNamespace('expireSingle');
Assert::same( 'p=plum', http_build_query($namespace->getIterator()) );


// small expiration
ob_start();
$namespace->setExpiration(100);
Assert::match("
Notice: The expiration time is greater than the session expiration 10 seconds in %a% on line %d%
", ob_get_clean());
