<?php

/**
 * Test: Nette\Application\Route with WithNamedParamsInQuery
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('?action=<presenter> & act=<action [a-z]+>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/?act=action');

testRouteIn($route, '/?act=default');



__halt_compiler() ?>

------EXPECT------
==> /?act=action

string(7) "Default"

array(2) {
	"action" => string(6) "action"
	"test" => string(9) "testvalue"
}

string(27) "/?act=action&test=testvalue"

==> /?act=default

string(7) "Default"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"
