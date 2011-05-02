<?php

/**
 * Test: Nette\Application\Routers\Route UTF-8 parameter.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param č>', array(
	'presenter' => 'Default',
));

testRouteIn($route, '/č', 'Default', array(
	'param' => 'č',
	'test' => 'testvalue',
), '/%C4%8D?test=testvalue');

testRouteIn($route, '/%C4%8D');

testRouteIn($route, '/');
