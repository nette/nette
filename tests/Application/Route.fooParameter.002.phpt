<?php

/**
 * Test: Nette\Application\Route with FooParameter
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('index<?.xml>/', array(
	'presenter' => 'DefaultPresenter',
));


testRouteIn($route, '/index.');

testRouteIn($route, '/index.xml');

testRouteIn($route, '/index.php');

testRouteIn($route, '/index');



__halt_compiler() ?>

------EXPECT------
==> /index.

not matched

==> /index.xml

"DefaultPresenter"

array(
	"test" => "testvalue"
)

"/index.xml/?test=testvalue"

==> /index.php

not matched

==> /index

"DefaultPresenter"

array(
	"test" => "testvalue"
)

"/index.xml/?test=testvalue"
