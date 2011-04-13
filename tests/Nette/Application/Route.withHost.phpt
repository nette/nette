<?php

/**
 * Test: Nette\Application\Routers\Route with WithHost
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('//<host>.<domain>/<path>', array(
	'presenter' => 'Default',
	'action' => 'default',
));

testRouteIn($route, '/abc', 'Default', array(
	'host' => 'example',
	'domain' => 'com',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');
