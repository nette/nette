<?php

/**
 * Test: Nette\Latte\MacroTokens::fetchWords()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$tokenizer = new MacroTokens('');
	Assert::same( array(),  $tokenizer->fetchWords() );
	Assert::same( '',  $tokenizer->joinAll() );
});


test(function() {
	$tokenizer = new MacroTokens('$1d-,a');
	Assert::same( array('$1d-'),  $tokenizer->fetchWords() );
	Assert::same( 'a',  $tokenizer->joinAll() );
});


test(function() {
	$tokenizer = new MacroTokens('"a:":$b" c" ,');
	Assert::same( array('"a:"', '$b" c"'),  $tokenizer->fetchWords() );
	Assert::same( '',  $tokenizer->joinAll() );
});
