<?php

/**
 * Test: Nette\Application\Route with WithParamsInQuery
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<action> ? <presenter>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/action/');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /action/

string(14) "querypresenter"

array(2) {
	"action" => string(6) "action"
	"test" => string(9) "testvalue"
}

string(47) "/action?test=testvalue&presenter=querypresenter"

==> /

string(14) "querypresenter"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(41) "/?test=testvalue&presenter=querypresenter"
