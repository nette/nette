<?php

/**
 * Test: Nette\Application\Routers\Route and full match parameter.
 *
 * @author     David Grudl
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<param .+>', array(
	'presenter' => 'Default',
));

testRouteIn($route, '/one', 'Default', array(
	'param' => 'one',
	'test' => 'testvalue',
), '/one?test=testvalue');

testRouteIn($route, '/');
