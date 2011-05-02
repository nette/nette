<?php

/**
 * Test: Nette\Application\Routers\Route and full match parameter.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param .+>', array(
	'presenter' => 'Default',
));

testRouteIn($route, '/one', 'Default', array(
	'param' => 'one',
	'test' => 'testvalue',
), '/one?test=testvalue');

testRouteIn($route, '/');
