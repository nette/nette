<?php

/**
 * Test: Nette\Application\Route with UrlEncoding
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/a%3Ab', 'Presenter', array(
	'param' => 'a:b',
	'test' => 'testvalue',
), '/a%3Ab?test=testvalue');
