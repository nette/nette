<?php

/**
 * Test: Nette\Latte\Runtime\Filters::optimizePhp()
 *
 * @author     David Grudl
 */

use Nette\Latte\Runtime\Filters,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/expected/Filters.optimizePhp().phtml');
Assert::match($expected, Filters::optimizePhp($input));
