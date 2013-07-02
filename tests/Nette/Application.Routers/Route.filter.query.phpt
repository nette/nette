<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route(' ? action=<presenter>', array(
	'presenter' => array(
		Route::FILTER_IN => function($s) {
			return strrev($s);
		},
		Route::FILTER_OUT => function($s) {
			return strtoupper(strrev($s));
		},
	),
));

testRouteIn($route, '/?action=abc', 'cba', array(
	'test' => 'testvalue',
), '/?test=testvalue&action=ABC');
