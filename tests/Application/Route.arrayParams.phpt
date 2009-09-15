<?php

/**
 * Test: Route with ArrayParams
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';

/*use Nette\Application\Route;*/


$route = new Route(' ? arr=<arr>', array(
	'presenter' => 'Default',
	'arr' => '',
));

testRoute($route, '/?arr[1]=1&arr[2]=2');


__halt_compiler();

------EXPECT------
==> /?arr[1]=1&arr[2]=2

string(7) "Default"

array(2) {
	"arr" => array(2) {
		1 => string(1) "1"
		2 => string(1) "2"
	}
	"test" => string(9) "testvalue"
}

string(42) "/?arr%5B1%5D=1&arr%5B2%5D=2&test=testvalue"
