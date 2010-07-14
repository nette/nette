<?php

/**
 * Test: Nette\Application\Route with WithParamsInQuery
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<action> ? <presenter>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/action/');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /action/

"querypresenter"

array(
	"action" => "action"
	"test" => "testvalue"
)

"/action?test=testvalue&presenter=querypresenter"

==> /

"querypresenter"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/?test=testvalue&presenter=querypresenter"
