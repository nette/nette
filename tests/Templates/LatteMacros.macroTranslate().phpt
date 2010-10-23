<?php

/**
 * Test: Nette\Templates\LatteMacros::macroTranslate()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;

// {_...}
Assert::same( '$template->translate(\'var\')',  $macros->macroTranslate('var', '') );
Assert::same( '$template->filter($template->translate(\'var\'))',  $macros->macroTranslate('var', '|filter') );
