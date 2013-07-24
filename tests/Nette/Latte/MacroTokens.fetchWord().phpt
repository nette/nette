<?php

/**
 * Test: Nette\Latte\MacroTokens::fetchWord()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$tokenizer = new MacroTokens('');
	Assert::false( $tokenizer->fetchWord() );
	Assert::same( '',  $tokenizer->joinAll() );
});


test(function() {
	$tokenizer = new MacroTokens('$1d-,a');
	Assert::same( '$1d-',  $tokenizer->fetchWord() );
	Assert::same( 'a',  $tokenizer->joinAll() );
});


test(function() {
	$tokenizer = new MacroTokens('"item\'1""item2"');
	Assert::same( '"item\'1""item2"',  $tokenizer->fetchWord() );
	Assert::same( '',  $tokenizer->joinAll() );
});
