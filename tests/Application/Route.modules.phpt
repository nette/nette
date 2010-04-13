<?php

/**
 * Test: Nette\Application\Route with Modules
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



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



__halt_compiler();

------EXPECT------
==> /abc

string(20) "module:submodule:Abc"

array(1) {
	"test" => string(9) "testvalue"
}

string(19) "/abc?test=testvalue"

==> /

not matched

==> [Homepage]

NULL

==> [Module:Homepage]

NULL

==> [Module:Submodule:Homepage]

string(27) "http://example.com/homepage"

==> /

string(24) "Module:Submodule:Default"

array(1) {
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

NULL

==> [Module:Submodule:Homepage]

string(27) "http://example.com/homepage"

==> /module.submodule

string(27) "Module:Submodule:AnyDefault"

array(1) {
	"test" => string(9) "testvalue"
}

string(33) "/module.submodule/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

string(34) "http://example.com/module/homepage"

==> [Module:Submodule:Homepage]

string(44) "http://example.com/module.submodule/homepage"

==> /module.submodule

string(24) "Module:Submodule:Default"

array(1) {
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"

==> [Homepage]

NULL

==> [Module:Homepage]

string(34) "http://example.com/module/homepage"

==> [Module:Submodule:Homepage]

string(44) "http://example.com/module.submodule/homepage"
