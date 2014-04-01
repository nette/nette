<?php

/**
 * Test: Latte\Engine and blocks.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
<head>
	<script src="nette.js"></script>
	<link rel="alternate">
</head>

	<link rel="alternate">
EOD
, $latte->renderToString(<<<EOD
<head>
	<script src="nette.js"></script>
	{include #meta}
</head>

{block meta}
	<link rel="alternate">
{/block}
EOD
));
