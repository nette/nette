<?php

/**
 * Test: Nette\Web\SessionNamespace undefined property.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Session;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$session = new Session;
$namespace = $session->getNamespace('one');

dump( isset($namespace->undefined) ); // False

dump( $namespace->undefined, 'Getting value of non-existent key' ); // Null

dump( http_build_query($namespace->getIterator()) );


__halt_compiler();

------EXPECT------
bool(FALSE)

Getting value of non-existent key: NULL

string(0) ""
