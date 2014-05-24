<?php

/**
 * Test: Nette\Application\Routers\Route with NoDefaultParams
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>/<action>/<extra>', array(
));

testRouteIn($route, '/presenter/action/12', 'Presenter', array(
	'action' => 'action',
	'extra' => '12',
	'test' => 'testvalue',
), NULL);
