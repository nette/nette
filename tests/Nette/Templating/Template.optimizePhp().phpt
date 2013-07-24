<?php

/**
 * Test: Nette\Templating\Helpers::optimizePhp()
 *
 * @author     David Grudl
 * @package    Nette\Templating
 */

use Nette\Templating\Helpers;


require __DIR__ . '/../bootstrap.php';


$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/Template.optimizePhp().expect');
Assert::match($expected, Helpers::optimizePhp($input));
