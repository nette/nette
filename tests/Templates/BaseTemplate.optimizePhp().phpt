<?php

/**
 * Test: Nette\Templates\BaseTemplate::optimizePhp()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\BaseTemplate;



require __DIR__ . '/../initialize.php';



$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/BaseTemplate.optimizePhp().expect');
Assert::match($expected, BaseTemplate::optimizePhp($input));
