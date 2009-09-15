<?php

/**
 * Test: Route with Secured
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';

/*use Nette\Application\Route;*/


$route = new Route('<param>', array(
	'presenter' => 'Presenter',
), Route::SECURED);


testRoute($route, '/any');


__halt_compiler();

------EXPECT------
==> /any

string(9) "Presenter"

array(2) {
	"param" => string(3) "any"
	"test" => string(9) "testvalue"
}

string(42) "https://admin.texy.info/any?test=testvalue"
