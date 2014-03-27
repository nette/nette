<?php

/**
 * Test: Nette\Latte\Helpers::optimizePhp()
 *
 * @author     David Grudl
 */

use Nette\Latte\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/expected/Helpers.optimizePhp().phtml');
Assert::match($expected, Helpers::optimizePhp($input));
