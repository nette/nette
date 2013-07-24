<?php

/**
 * Test: Nette\Http\SessionSection basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Session;


require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', TEMP_DIR);


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

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
