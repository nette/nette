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

Assert::match(<<<EOD
<div>
<div id="">	hello
</div></div>
EOD
, $latte->renderToString(<<<EOD
<div>
	{snippet abc}
	hello
	{/snippet}
</div>
EOD
, array('_control' => new MockControl)));
