<?php

/**
 * Test: Nette\Application\Route with CombinedUrlParam
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('extra<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/extrapresenter/action/');

testRouteIn($route, '/extradefault/default/');

testRouteIn($route, '/extra');

testRouteIn($route, '/');



__halt_compiler();

------EXPECT------
==> /presenter/action/

not matched

==> /extrapresenter/action/

string(9) "Presenter"

array(2) {
	"action" => string(6) "action"
	"test" => string(9) "testvalue"
}

string(37) "/extrapresenter/action?test=testvalue"

==> /extradefault/default/

string(7) "Default"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(21) "/extra?test=testvalue"

==> /extra

string(7) "Default"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(21) "/extra?test=testvalue"

==> /

not matched
