<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



Route::addStyle('#xlat', 'presenter');
Route::setStyleProperty('#xlat', Route::FILTER_TABLE, array(
	'produkt' => 'Product',
	'kategorie' => 'Category',
	'zakaznik' => 'Customer',
	'kosik' => 'Basket',
));

$route = new Route(' ? action=<presenter #xlat>', array());

testRouteIn($route, '/?action=kategorie', 'Category', array(
	'test' => 'testvalue',
), '/?test=testvalue&action=kategorie');
