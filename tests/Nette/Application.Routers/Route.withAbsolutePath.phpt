<?php

/**
 * Test: Nette\Application\Routers\Route with WithAbsolutePath
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('/<abspath>/', array(
	'presenter' => 'Default',
	'action' => 'default',
));

testRouteIn($route, '/abc', 'Default', array(
	'abspath' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc/?test=testvalue');
