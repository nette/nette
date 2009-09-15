<?php

/**
 * Test: Route with CombinedUrlParam
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';

/*use Nette\Application\Route;*/


$route = new Route('extra<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRoute($route, '/presenter/action/');

testRoute($route, '/extrapresenter/action/');

testRoute($route, '/extradefault/default/');

testRoute($route, '/extra');

testRoute($route, '/');


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
