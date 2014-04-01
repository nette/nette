<?php

/**
 * Test: Latte\Engine and invalid UTF-8.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile("\xAA");
}, 'InvalidArgumentException', '%a% UTF-8 %a%');
