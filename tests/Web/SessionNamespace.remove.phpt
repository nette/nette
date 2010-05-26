<?php

/**
 * Test: Nette\Web\SessionNamespace remove.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Session;



require __DIR__ . '/../NetteTest/initialize.php';



$session = new Session;
$namespace = $session->getNamespace('three');
$namespace->a = 'apple';
$namespace->p = 'papaya';
$namespace['c'] = 'cherry';

$namespace = $session->getNamespace('three');
dump( http_build_query($namespace->getIterator()) );

output('removing');
$namespace->remove();
dump( http_build_query($namespace->getIterator()) );



__halt_compiler() ?>

------EXPECT------
string(25) "a=apple&p=papaya&c=cherry"

removing

string(0) ""
