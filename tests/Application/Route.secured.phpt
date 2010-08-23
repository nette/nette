<?php

/**
 * Test: Nette\Application\Route with Secured
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<param>', array(
	'presenter' => 'Presenter',
), Route::SECURED);

testRouteIn($route, '/any', 'Presenter', array(
	'param' => 'any',
	'test' => 'testvalue',
), 'https://example.com/any?test=testvalue');
