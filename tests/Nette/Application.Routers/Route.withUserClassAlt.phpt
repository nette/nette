<?php

/**
 * Test: Nette\Application\Routers\Route with WithUserClassAlt
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<id>', array(
	'id' => array(
		Route::PATTERN => '\d{1,3}',
	),
));

testRouteIn($route, '/presenter/12/', 'Presenter', array(
	'id' => '12',
	'test' => 'testvalue',
), '/presenter/12?test=testvalue');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');
