<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroIfset()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new DefaultMacros;

// {ifset ... }
Assert::same( '$var',  $macros->macroIfset('$var') );
Assert::same( '$item->var["test"]',  $macros->macroIfset('$item->var["test"]') );
Assert::same( '$_l->blocks["block"]',  $macros->macroIfset('#block') );
Assert::same( '$item->var["#test"], $_l->blocks["block"]',  $macros->macroIfset('$item->var["#test"], #block') );
