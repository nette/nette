<?php

/**
 * Test: Nette\Templates\BaseTemplate::optimizePhp()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\BaseTemplate;



require __DIR__ . '/../bootstrap.php';



$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/BaseTemplate.optimizePhp().expect');
Assert::match($expected, BaseTemplate::optimizePhp($input));
