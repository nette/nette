<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>', array(
	'presenter' => array(
		Route::FILTER_IN => function($s) {
			return strrev($s);
		},
		Route::FILTER_OUT => function($s) {
			return strtoupper(strrev($s));
		},
	),
));

testRouteIn($route, '/abc/', 'cba', array(
	'test' => 'testvalue',
), '/ABC?test=testvalue');
