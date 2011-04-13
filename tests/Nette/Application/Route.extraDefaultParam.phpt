<?php

/**
 * Test: Nette\Application\Routers\Route with ExtraDefaultParam
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>/<id \d{1,3}>/', array(
	'extra' => NULL,
));

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12', 'Presenter', array(
	'action' => 'action',
	'id' => '12',
	'extra' => NULL,
	'test' => 'testvalue',
), '/presenter/action/12/?test=testvalue');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');
