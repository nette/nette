<?php

/**
 * Test: Nette\Latte\MacroTokenizer::fetchWords()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\MacroTokenizer;



require __DIR__ . '/../bootstrap.php';



test(function() {
	$tokenizer = new MacroTokenizer('');
	Assert::same( array(),  $tokenizer->fetchWords() );
	Assert::same( FALSE,  $tokenizer->fetchAll() );
});



test(function() {
	$tokenizer = new MacroTokenizer('$1d-,a');
	Assert::same( array('$1d-'),  $tokenizer->fetchWords() );
	Assert::same( 'a',  $tokenizer->fetchAll() );
});



test(function() {
	$tokenizer = new MacroTokenizer('"a:":$b" c" ,');
	Assert::same( array('"a:"', '$b" c"'),  $tokenizer->fetchWords() );
	Assert::same( FALSE,  $tokenizer->fetchAll() );
});
