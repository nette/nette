<?php

/**
 * Test: Nette\Latte\PhpWriter::formatWord()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\PhpWriter,
	Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


$writer = new PhpWriter(new MacroTokens(''));


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
Assert::same( "'var'.'var'",  $writer->formatWord("var.'var'") );
Assert::same( "\$var['var']",  $writer->formatWord('$var[var]') );
Assert::same( '$x["[x]"]',  $writer->formatWord('$x["[x]"]') );


Assert::exception(function() use ($writer) {
	$writer->formatWord("'var\"");
}, 'Nette\Utils\TokenizerException', "Unexpected ''var\"' on line 1, column 1.");
