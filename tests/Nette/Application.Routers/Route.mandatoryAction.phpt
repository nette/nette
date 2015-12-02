<?php

/**
 * Test: Nette\Application\Routers\Route and non-optional action.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<action>', 'Default:');

testRouteIn($route, '/default', 'Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/default?test=testvalue');

testRouteIn($route, '/', NULL);


$route = new Route('<action>', 'Front:Default:');

testRouteIn($route, '/default', 'Front:Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/default?test=testvalue');
