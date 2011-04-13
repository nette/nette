<?php

/**
 * Test: Nette\Templating\Template::optimizePhp()
 *
 * @author     David Grudl
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Templating\Template;



require __DIR__ . '/../bootstrap.php';



$input = file_get_contents(__DIR__ . '/templates/optimize.latte');
$expected = file_get_contents(__DIR__ . '/Template.optimizePhp().expect');
Assert::match($expected, Template::optimizePhp($input));
