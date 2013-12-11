<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>', array(
	NULL => array(
		Route::FILTER_IN => function(array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return NULL;
			}
			$arr['presenter'] .= '.in';
			$arr['param'] .= '.in';
			return $arr;
		},
		Route::FILTER_OUT => function(array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return NULL;
			}
			$arr['presenter'] .= '.out';
			$arr['param'] .= '.out';
			return $arr;
		},
	),
));

testRouteIn($route, '/abc?param=1', 'Abc.in', array(
	'param' => '1.in',
	'test' => 'testvalue',
), '/abc.in.out?param=1.in.out&test=testvalue');

testRouteIn($route, '/cde?param=1');

\Tester\Assert::null(testRouteOut($route, 'Cde'));
