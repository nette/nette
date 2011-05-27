<?php

/**
 * Test: Nette\Latte\PhpWriter::formatWord()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\PhpWriter;



require __DIR__ . '/../bootstrap.php';


$writer = new PhpWriter;


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
