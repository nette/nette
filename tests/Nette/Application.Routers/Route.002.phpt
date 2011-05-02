<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('index.php', array(
	'action' => 'default',
));

testRouteIn($route, '/index.php', 'querypresenter', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/index.php?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/');
