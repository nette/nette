<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>', array(
	'presenter' => array(
		Route::FILTER_TABLE => array(
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		),
	),
));

testRouteIn($route, '/kategorie/', 'Category', array(
	'test' => 'testvalue',
), '/kategorie?test=testvalue');

testRouteIn($route, '/other/', 'Other', array(
	'test' => 'testvalue',
), '/other?test=testvalue');
