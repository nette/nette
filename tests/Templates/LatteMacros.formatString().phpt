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



Assert::same( '""',  LatteMacros::formatString('') );
Assert::same( '" "',  LatteMacros::formatString(' ') );
Assert::same( "0",  LatteMacros::formatString('0') );
Assert::same( "-0.0",  LatteMacros::formatString('-0.0') );
Assert::same( '"symbol"',  LatteMacros::formatString('symbol') );
Assert::same( "\$var",  LatteMacros::formatString('$var') );
Assert::same( '"symbol$var"',  LatteMacros::formatString('symbol$var') );
Assert::same( "'var'",  LatteMacros::formatString("'var'") );
Assert::same( '"var"',  LatteMacros::formatString('"var"') );
Assert::same( '"v\\"ar"',  LatteMacros::formatString('"v\\"ar"') );
Assert::same( "'var\"",  LatteMacros::formatString("'var\"") );
