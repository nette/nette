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
<p><div id="">hello</div> world</p>
EOD
, $latte->renderToString(<<<EOD
<p>{snippet abc}hello{/snippet} world</p>
EOD
, array('_control' => new MockControl)));
