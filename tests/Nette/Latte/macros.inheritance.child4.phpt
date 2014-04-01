<?php

/**
 * Test: Latte\Engine: {extends ...} test IV.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
	Content
EOD
, $latte->renderToString(file_get_contents(__DIR__ . '/templates/inheritance.child4.latte')));
