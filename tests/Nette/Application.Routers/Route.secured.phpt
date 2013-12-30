<?php

/**
 * Test: Nette\Application\Routers\Route with Secured
 *
 * @author     David Grudl
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<param>', array(
	'presenter' => 'Presenter',
), Route::SECURED);

testRouteIn($route, '/any', 'Presenter', array(
	'param' => 'any',
	'test' => 'testvalue',
), 'https://example.com/any?test=testvalue');
