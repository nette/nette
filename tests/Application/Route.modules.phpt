<?php

/**
 * Test: Nette\Application\Route with Modules
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>', array(
	'module' => 'module:submodule',
));

testRouteIn($route, '/abc');
testRouteIn($route, '/');
testRouteOut($route, 'Homepage');
testRouteOut($route, 'Module:Homepage');
testRouteOut($route, 'Module:Submodule:Homepage');



$route = new Route('<presenter>', array(
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
));

testRouteIn($route, '/');
testRouteOut($route, 'Homepage');
testRouteOut($route, 'Module:Homepage');
testRouteOut($route, 'Module:Submodule:Homepage');



$route = new Route('<module>/<presenter>', array(
	'presenter' => 'AnyDefault',
));

testRouteIn($route, '/module.submodule');
testRouteOut($route, 'Homepage');
testRouteOut($route, 'Module:Homepage');
testRouteOut($route, 'Module:Submodule:Homepage');



$route = new Route('<module>/<presenter>', array(
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
));

testRouteIn($route, '/module.submodule');
testRouteOut($route, 'Homepage');
testRouteOut($route, 'Module:Homepage');
testRouteOut($route, 'Module:Submodule:Homepage');



__halt_compiler() ?>

------EXPECT------
==> /abc

"module:submodule:Abc"

array(
	"test" => "testvalue"
)

"/abc?test=testvalue"

==> /

not matched

==> [Homepage]

NULL

==> [Module:Homepage]

NULL

==> [Module:Submodule:Homepage]

"http://example.com/homepage"

==> /

"Module:Submodule:Default"

array(
	"test" => "testvalue"
)

"/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

NULL

==> [Module:Submodule:Homepage]

"http://example.com/homepage"

==> /module.submodule

"Module:Submodule:AnyDefault"

array(
	"test" => "testvalue"
)

"/module.submodule/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

"http://example.com/module/homepage"

==> [Module:Submodule:Homepage]

"http://example.com/module.submodule/homepage"

==> /module.submodule

"Module:Submodule:Default"

array(
	"test" => "testvalue"
)

"/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

"http://example.com/module/homepage"

==> [Module:Submodule:Homepage]

"http://example.com/module.submodule/homepage"
