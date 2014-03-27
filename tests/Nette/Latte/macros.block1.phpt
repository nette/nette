<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
	<div id="main">
		side
	</div> <!-- /main -->

side
	<div id="main">
		side
	</div> <!-- /main -->
EOD
, $latte->renderToString(<<<EOD
	{block main}
	<div id="main">
		{block sidebar}side{/block}
	</div> <!-- /main -->
	{/block}

	{include sidebar}

{include #main}
EOD
));
