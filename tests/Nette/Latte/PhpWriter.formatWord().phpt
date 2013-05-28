<?php

/**
 * Test: Nette\Latte\PhpWriter::formatWord()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\PhpWriter;
use Nette\Latte\MacroTokenizer;



require __DIR__ . '/../bootstrap.php';


$writer = new PhpWriter(new MacroTokenizer(''));


Assert::same( '""',  $writer->formatWord('') );
Assert::same( '" "',  $writer->formatWord(' ') );
Assert::same( "0",  $writer->formatWord('0') );
Assert::same( "-0.0",  $writer->formatWord('-0.0') );
Assert::same( '"symbol"',  $writer->formatWord('symbol') );
Assert::same( "\$var",  $writer->formatWord('$var') );
Assert::same( '"symbol$var"',  $writer->formatWord('symbol$var') );
Assert::same( "'var'",  $writer->formatWord("'var'") );
Assert::same( '"var"',  $writer->formatWord('"var"') );
Assert::same( '"v\\"ar"',  $writer->formatWord('"v\\"ar"') );
Assert::same( "'var\"",  $writer->formatWord("'var\"") );
Assert::same( "var.'var'",  $writer->formatWord("var.'var'") );
