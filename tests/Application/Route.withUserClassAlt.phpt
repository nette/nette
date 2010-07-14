<?php

/**
 * Test: Nette\Application\Route with WithUserClassAlt
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<id>', array(
	'id' => array(
		Route::PATTERN => '\d{1,3}',
	),
), Route::FULL_META);

testRouteIn($route, '/presenter/12/');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/12/

"Presenter"

array(
	"id" => "12"
	"test" => "testvalue"
)

"/presenter/12?test=testvalue"

==> /presenter/1234

not matched

==> /presenter/

not matched
