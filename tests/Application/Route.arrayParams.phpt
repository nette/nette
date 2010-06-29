<?php

/**
 * Test: Nette\Application\Route with ArrayParams
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route(' ? arr=<arr>', array(
	'presenter' => 'Default',
	'arr' => '',
));

testRouteIn($route, '/?arr[1]=1&arr[2]=2');



__halt_compiler() ?>

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
