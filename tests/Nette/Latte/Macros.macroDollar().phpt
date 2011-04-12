<?php

/**
 * Test: Nette\Templates\LatteMacros::macroDollar()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;

// {$...}
Assert::same( '$var',  $macros->macroDollar('var', '') );
Assert::same( '$$var',  $macros->macroDollar('$var', '') );
Assert::same( '$template->filter($var)',  $macros->macroDollar('var', 'filter') );
