<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route(' ? action=<presenter>', array(
	'presenter' => array(
		Route::FILTER_TABLE => array(
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		),
	),
));

testRouteIn($route, '/?action=kategorie', 'Category', array(
	'test' => 'testvalue',
), '/?test=testvalue&action=kategorie');
