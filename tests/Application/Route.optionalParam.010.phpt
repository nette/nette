<?php

/**
 * Test: Nette\Application\Route with module in optional sequence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';


$route = new Route('[<module admin|image>/]<presenter>/<action>', array(
	'module' => 'Front',
	'presenter' => 'Homepage',
	'action' => 'default',
));

testRouteIn($route, '/one');

testRouteIn($route, '/admin/one');

testRouteIn($route, '/one/admin');



__halt_compiler();

------EXPECT------
==> /one

string(9) "Front:One"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(20) "/one/?test=testvalue"

==> /admin/one

string(9) "Admin:One"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(26) "/admin/one/?test=testvalue"

==> /one/admin

string(9) "Front:One"

array(2) {
	"action" => string(5) "admin"
	"test" => string(9) "testvalue"
}

string(25) "/one/admin?test=testvalue"
