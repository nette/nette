<?php

/**
 * Test: Nette\Http\SessionSection::setExpiration()
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$session->setExpiration('+10 seconds');

// try to expire whole namespace
$namespace = $session->getSection('expire');
$namespace->a = 'apple';
$namespace->p = 'pear';
$namespace['o'] = 'orange';
$namespace->setExpiration('+ 1 seconds');

$session->close();
sleep(2);
$session->start();

$namespace = $session->getSection('expire');
Assert::same( '', http_build_query($namespace->getIterator()) );


// try to expire only 1 of the keys
$namespace = $session->getSection('expireSingle');
$namespace->setExpiration(1, 'g');
$namespace->g = 'guava';
$namespace->p = 'plum';

$session->close();
sleep(2);
$session->start();

$namespace = $session->getSection('expireSingle');
Assert::same( 'p=plum', http_build_query($namespace->getIterator()) );


// small expiration
Assert::throws(function() use ($namespace) {
	$namespace->setExpiration(100);
}, 'TestErrorException', "The expiration time is greater than the session expiration 10 seconds in %a%:%d%");
