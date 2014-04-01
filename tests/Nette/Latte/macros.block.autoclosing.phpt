<?php

/**
 * Test: Latte\Engine: {block} autoclosing
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
Block

EOD

, $latte->renderToString(<<<EOD
{block}
Block

EOD
));
