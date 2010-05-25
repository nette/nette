<?php

/**
 * Test: Nette\Application\Route UTF-8 parameter.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<param č>', array(
	'presenter' => 'Default',
));

testRouteIn($route, '/č');

testRouteIn($route, '/%C4%8D');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /č

string(7) "Default"

array(2) {
	"param" => string(2) "č"
	"test" => string(9) "testvalue"
}

string(22) "/%C4%8D?test=testvalue"

==> /%C4%8D

string(7) "Default"

array(2) {
	"param" => string(2) "č"
	"test" => string(9) "testvalue"
}

string(22) "/%C4%8D?test=testvalue"

==> /

not matched
