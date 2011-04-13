<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroDollar()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new DefaultMacros;

// {$...}
Assert::same( '$var',  $macros->macroDollar('var', '') );
Assert::same( '$$var',  $macros->macroDollar('$var', '') );
Assert::same( '$template->filter($var)',  $macros->macroDollar('var', 'filter') );
