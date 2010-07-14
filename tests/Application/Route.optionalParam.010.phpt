<?php

/**
 * Test: Nette\Application\Route with module in optional sequence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<module admin|image>/]<presenter>/<action>', array(
	'module' => 'Front',
	'presenter' => 'Homepage',
	'action' => 'default',
));

testRouteIn($route, '/one');

testRouteIn($route, '/admin/one');

testRouteIn($route, '/one/admin');



__halt_compiler() ?>

------EXPECT------
==> /one

"Front:One"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/one/?test=testvalue"

==> /admin/one

"Admin:One"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/admin/one/?test=testvalue"

==> /one/admin

"Front:One"

array(
	"action" => "admin"
	"test" => "testvalue"
)

"/one/admin?test=testvalue"
