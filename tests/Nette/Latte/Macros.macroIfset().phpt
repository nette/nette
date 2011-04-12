<?php

/**
 * Test: Nette\Templates\LatteMacros::macroIfset()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;

// {ifset ... }
Assert::same( '$var',  $macros->macroIfset('$var') );
Assert::same( '$item->var["test"]',  $macros->macroIfset('$item->var["test"]') );
Assert::same( '$_l->blocks["block"]',  $macros->macroIfset('#block') );
Assert::same( '$item->var["#test"], $_l->blocks["block"]',  $macros->macroIfset('$item->var["#test"], #block') );
