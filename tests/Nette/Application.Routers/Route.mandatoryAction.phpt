<?php

/**
 * Test: Nette\Application\Routers\Route and non-optional action.
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<action>', 'Default:');

testRouteIn($route, '/default', 'Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/default?test=testvalue');
