<?php

/**
 * Test: Nette\Application\Route with WithNamedParamsInQuery
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('?action=<presenter> & act=<action [a-z]+>', array(
	'presenter' => 'Default',
	'action' => 'default',
));

testRouteIn($route, '/?act=action', 'Default', array(
	'action' => 'action',
	'test' => 'testvalue',
), '/?act=action&test=testvalue');

testRouteIn($route, '/?act=default', 'Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/?test=testvalue');
