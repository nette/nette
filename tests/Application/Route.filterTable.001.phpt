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

$route = new Route('<presenter #xlat>', array());

testRoute($route, '/kategorie/');

testRoute($route, '/other/');


__halt_compiler();

------EXPECT------
==> /kategorie/

string(8) "Category"

array(1) {
	"test" => string(9) "testvalue"
}

string(25) "/kategorie?test=testvalue"

==> /other/

string(5) "Other"

array(1) {
	"test" => string(9) "testvalue"
}

string(21) "/other?test=testvalue"
