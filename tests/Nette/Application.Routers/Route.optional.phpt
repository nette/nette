<?php

/**
 * Test: Nette\Application\Routers\Route with optional sequence.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('index[.html]', array(
));

testRouteIn($route, '/index.html', 'querypresenter', array(
	'test' => 'testvalue',
), '/index?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/index', 'querypresenter', array(
	'test' => 'testvalue',
), '/index?test=testvalue&presenter=querypresenter');
