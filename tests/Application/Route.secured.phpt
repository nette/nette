<?php

/**
 * Test: Nette\Application\Route with Secured
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param>', array(
	'presenter' => 'Presenter',
), Route::SECURED);


testRouteIn($route, '/any');



__halt_compiler() ?>

------EXPECT------
==> /any

string(9) "Presenter"

array(2) {
	"param" => string(3) "any"
	"test" => string(9) "testvalue"
}

string(38) "https://example.com/any?test=testvalue"
