<?php

/**
 * Test: Nette\Application\Route with CamelcapsVsDash
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<presenter>', array(
	'presenter' => 'DefaultPresenter',
));

testRouteIn($route, '/abc-x-y-z');

testRouteIn($route, '/');

testRouteIn($route, '/--');



__halt_compiler();

------EXPECT------
==> /abc-x-y-z

string(6) "AbcXYZ"

array(1) {
	"test" => string(9) "testvalue"
}

string(25) "/abc-x-y-z?test=testvalue"

==> /

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"

==> /--

not matched
