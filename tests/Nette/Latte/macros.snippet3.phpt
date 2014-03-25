<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockControl
{
	function __call($name, $args)
	{
	}
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$params['_control'] = new MockControl;

Assert::match(<<<EOD
<p id="">hello</p>
EOD
, $latte->renderToString(<<<EOD
<p n:inner-snippet="abc">hello</p>
EOD
, $params));


Assert::match(<<<EOD
<p id="">hello</p>
EOD
, $latte->renderToString(<<<EOD
<p n:snippet="abc">hello</p>
EOD
, $params));
