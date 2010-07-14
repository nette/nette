<?php

/**
 * Test: Nette\Application\Route with optional sequence precedence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<one>/][<two>]', array(
));

testRouteIn($route, '/one');


T::note();


$route = new Route('[<one>/]<two>', array(
	'two' => NULL,
));

testRouteIn($route, '/one');



__halt_compiler() ?>

------EXPECT------
==> /one

"querypresenter"

array(
	"one" => "one"
	"two" => NULL
	"test" => "testvalue"
)

"/one/?test=testvalue&presenter=querypresenter"

===

==> /one

"querypresenter"

array(
	"one" => "one"
	"two" => NULL
	"test" => "testvalue"
)

"/one/?test=testvalue&presenter=querypresenter"
