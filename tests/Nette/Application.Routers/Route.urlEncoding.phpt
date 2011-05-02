<?php

/**
 * Test: Nette\Application\Routers\Route with UrlEncoding
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/a%3Ab', 'Presenter', array(
	'param' => 'a:b',
	'test' => 'testvalue',
), '/a%3Ab?test=testvalue');
