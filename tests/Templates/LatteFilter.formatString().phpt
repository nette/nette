<?php

/**
 * Test: Nette\Templates\LatteFilter::formatString()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



Assert::same( '""',  LatteFilter::formatString('') );
Assert::same( '" "',  LatteFilter::formatString(' ') );
Assert::same( "0",  LatteFilter::formatString('0') );
Assert::same( "-0.0",  LatteFilter::formatString('-0.0') );
Assert::same( '"symbol"',  LatteFilter::formatString('symbol') );
Assert::same( "\$var",  LatteFilter::formatString('$var') );
Assert::same( '"symbol$var"',  LatteFilter::formatString('symbol$var') );
Assert::same( "'var'",  LatteFilter::formatString("'var'") );
Assert::same( '"var"',  LatteFilter::formatString('"var"') );
Assert::same( '"v\\"ar"',  LatteFilter::formatString('"v\\"ar"') );
Assert::same( "'var\"",  LatteFilter::formatString("'var\"") );
