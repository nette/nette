<?php

/**
 * Test: snippets.
 *
 * @author     David Grudl
 */

use Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockControl
{
	function __call($name, $args)
	{
	}
}


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());
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
