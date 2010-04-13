<?php

/**
 * Test: Nette\Application\Route default usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<presenter>/<action>/<id \d{1,3}>', array(
	'action' => 'default',
	'id' => NULL,
));

testRouteOut($route, 'Homepage');

testRouteOut($route, 'Homepage', array('action' => 'default'));

testRouteOut($route, 'Homepage', array('id' => 'word'));

testRouteOut($route, 'Front:Homepage');

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12/');

testRouteIn($route, '/presenter/action/12');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter/action');

testRouteIn($route, '/presenter/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');



__halt_compiler();

------EXPECT------
==> [Homepage]

string(28) "http://example.com/homepage/"

==> [Homepage]

string(28) "http://example.com/homepage/"

==> [Homepage]

NULL

==> [Front:Homepage]

string(34) "http://example.com/front.homepage/"

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

string(33) "/presenter/action/?test=testvalue"

==> /presenter/action

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(33) "/presenter/action/?test=testvalue"

==> /presenter/

string(9) "Presenter"

array(3) {
	"action" => string(7) "default"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(26) "/presenter/?test=testvalue"

==> /presenter

string(9) "Presenter"

array(3) {
	"action" => string(7) "default"
	"id" => NULL
	"test" => string(9) "testvalue"
}

string(26) "/presenter/?test=testvalue"

==> /

not matched
