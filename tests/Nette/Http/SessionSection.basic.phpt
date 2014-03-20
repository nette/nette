<?php

/**
 * Test: Nette\Http\SessionSection basic usage.
 *
 * @author     David Grudl
 */

use Nette\Http\Session,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$session = new Session(new Nette\Http\Request(new Nette\Http\UrlScript), new Nette\Http\Response);

$namespace = $session->getSection('one');
$namespace->a = 'apple';
$namespace->p = 'pear';
$namespace['o'] = 'orange';

foreach ($namespace as $key => $val) {
	$tmp[] = "$key=$val";
}
Assert::same( array(
	'a=apple',
	'p=pear',
	'o=orange',
), $tmp );


Assert::true( isset($namespace['p']) );
Assert::true( isset($namespace->o) );
Assert::false( isset($namespace->undefined) );

unset($namespace['a']);
unset($namespace->p);
unset($namespace->o);
unset($namespace->undef);

Assert::same( '', http_build_query($namespace->getIterator()) );
