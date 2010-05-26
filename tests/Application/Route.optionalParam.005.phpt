<?php

/**
 * Test: Nette\Application\Route and auto-optional as optional sequences II.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>[/<action>[/<id \d{1,3}>]]', array(
	'action' => 'default',
));

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12/');

testRouteIn($route, '/presenter/action/12');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter/action');

testRouteIn($route, '/presenter/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/action/12/any

not matched

==> /presenter/action/12/

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"id" => string(2) "12"
	"test" => string(9) "testvalue"
}

string(35) "/presenter/action/12?test=testvalue"

==> /presenter/action/12

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"id" => string(2) "12"
	"test" => string(9) "testvalue"
}

string(35) "/presenter/action/12?test=testvalue"

==> /presenter/action/1234

not matched

==> /presenter/action/

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(32) "/presenter/action?test=testvalue"

==> /presenter/action

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(32) "/presenter/action?test=testvalue"

==> /presenter/

string(9) "Presenter"

array(3) {
	"action" => string(7) "default"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(25) "/presenter?test=testvalue"

==> /presenter

string(9) "Presenter"

array(3) {
	"action" => string(7) "default"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(25) "/presenter?test=testvalue"

==> /

not matched
