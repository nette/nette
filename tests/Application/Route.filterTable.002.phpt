<?php

/**
 * Test: Route with FilterTable
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';

/*use Nette\Application\Route;*/


Route::addStyle('#xlat', 'presenter');
Route::setStyleProperty('#xlat', Route::FILTER_TABLE, array(
	'produkt' => 'Product',
	'kategorie' => 'Category',
	'zakaznik' => 'Customer',
	'kosik' => 'Basket',
));

$route = new Route(' ? action=<presenter #xlat>', array());

testRoute($route, '/?action=kategorie');


__halt_compiler();

------EXPECT------
==> /?action=kategorie

string(8) "Category"

array(1) {
	"test" => string(9) "testvalue"
}

string(33) "/?test=testvalue&action=kategorie"
