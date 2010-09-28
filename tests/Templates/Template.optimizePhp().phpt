<?php

/**
 * Test: Nette\Templates\Template::optimizePhp()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template;



require __DIR__ . '/../bootstrap.php';



$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/Template.optimizePhp().expect');
Assert::match($expected, Template::optimizePhp($input));
