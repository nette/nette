<?php

/**
 * Test: Nette\Application\Routers\Route with CamelcapsVsDash
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>', array(
	'presenter' => 'DefaultPresenter',
));

testRouteIn($route, '/abc-x-y-z', 'AbcXYZ', array(
	'test' => 'testvalue',
), '/abc-x-y-z?test=testvalue');

testRouteIn($route, '/', 'DefaultPresenter', array(
	'test' => 'testvalue',
), '/?test=testvalue');

testRouteIn($route, '/--');
