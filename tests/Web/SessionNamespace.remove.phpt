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



require __DIR__ . '/../initialize.php';



$session = new Session;
$namespace = $session->getNamespace('three');
$namespace->a = 'apple';
$namespace->p = 'papaya';
$namespace['c'] = 'cherry';

$namespace = $session->getNamespace('three');
T::dump( http_build_query($namespace->getIterator()) );

T::note('removing');
$namespace->remove();
T::dump( http_build_query($namespace->getIterator()) );



__halt_compiler() ?>

------EXPECT------
"a=apple&p=papaya&c=cherry"

removing

""
