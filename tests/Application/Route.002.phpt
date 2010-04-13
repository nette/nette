<?php

/**
 * Test: Nette\Application\Route default usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('index.php', array(
	'action' => 'default',
));

testRouteIn($route, '/index.php');

testRouteIn($route, '/');



__halt_compiler();

------EXPECT------
==> /index.php

string(14) "querypresenter"

array(2) {
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(50) "/index.php?test=testvalue&presenter=querypresenter"

==> /

not matched
