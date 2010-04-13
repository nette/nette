<?php

/**
 * Test: Nette\Application\Route with WithDefaultPresenterAndAction
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/default/default/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');



__halt_compiler();

------EXPECT------
==> /presenter/action/

string(9) "Presenter"

array(2) {
	"action" => string(6) "action"
	"test" => string(9) "testvalue"
}

string(32) "/presenter/action?test=testvalue"

==> /default/default/

string(7) "Default"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"

==> /presenter

string(9) "Presenter"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(26) "/presenter/?test=testvalue"

==> /

string(7) "Default"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"
