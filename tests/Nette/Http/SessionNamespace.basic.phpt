<?php

/**
 * Test: Nette\Http\SessionNamespace basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$namespace = $session->getNamespace('one');
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
