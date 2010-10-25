<?php

/**
 * Test: Nette\Templates\LatteMacros::formatString()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new LatteMacros;

Assert::same( '""',  $latte->formatString('') );
Assert::same( '" "',  $latte->formatString(' ') );
Assert::same( "0",  $latte->formatString('0') );
Assert::same( "-0.0",  $latte->formatString('-0.0') );
Assert::same( '"symbol"',  $latte->formatString('symbol') );
Assert::same( "\$var",  $latte->formatString('$var') );
Assert::same( '"symbol$var"',  $latte->formatString('symbol$var') );
Assert::same( "'var'",  $latte->formatString("'var'") );
Assert::same( '"var"',  $latte->formatString('"var"') );
Assert::same( '"v\\"ar"',  $latte->formatString('"v\\"ar"') );
Assert::same( "'var\"",  $latte->formatString("'var\"") );
