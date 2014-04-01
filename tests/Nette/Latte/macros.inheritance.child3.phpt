<?php

/**
 * Test: Latte\Engine: {extends ...} test III.
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
, $latte->renderToString(file_get_contents(__DIR__ . '/templates/inheritance.child3.latte')));
