<?php

/**
 * Test: Nette\Application\Route with CamelcapsVsDash
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

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
