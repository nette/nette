<?php

/**
 * Test: Route with Modules
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

testRoute($route, '/abc');

testRoute($route, '/');


$route = new Route('<presenter>', array(
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
));

testRoute($route, '/');





$route = new Route('<module>/<presenter>', array(
	'presenter' => 'AnyDefault',
));

testRoute($route, '/module.submodule');



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

==> /

string(24) "Module:Submodule:Default"

array(1) {
	"test" => string(9) "testvalue"
}

string(16) "/?test=testvalue"

==> /module.submodule

string(27) "Module:Submodule:AnyDefault"

array(1) {
	"test" => string(9) "testvalue"
}

string(33) "/module.submodule/?test=testvalue"
