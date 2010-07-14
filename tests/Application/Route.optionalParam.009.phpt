<?php

/**
 * Test: Nette\Application\Route with "required" optional sequence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('index[!.html]', array(
));

testRouteIn($route, '/index.html');

testRouteIn($route, '/index');



__halt_compiler() ?>

------EXPECT------
==> /index.html

"querypresenter"

array(
	"test" => "testvalue"
)

"/index.html?test=testvalue&presenter=querypresenter"

==> /index

"querypresenter"

array(
	"test" => "testvalue"
)

"/index.html?test=testvalue&presenter=querypresenter"
