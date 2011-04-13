<?php

/**
 * Test: Nette\Application\Routers\Route with OneWay
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
), Route::ONE_WAY);

testRouteIn($route, '/presenter/action/', 'Presenter', array(
	'action' => 'action',
	'test' => 'testvalue',
), NULL);
