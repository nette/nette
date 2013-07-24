<?php

/**
 * Test: Nette\Latte\MacroTokens::append()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


test(function() { // constructor
	$tokenizer = new MacroTokens('hello world');
	Assert::same( 3, count($tokenizer->tokens) );

	$tokenizer2 = new MacroTokens($tokenizer->tokens);
	Assert::same( $tokenizer2->tokens, $tokenizer->tokens );

	$tokenizer3 = new MacroTokens(NULL);
	Assert::same( 0, count($tokenizer3->tokens) );
});


test(function() { // append
	$tokenizer = new MacroTokens('hello ');

	$res = $tokenizer->append('world!');
	Assert::same( $tokenizer, $res );
	Assert::same( 'hello world!', $tokenizer->joinAll() );
	Assert::same( 4, count($tokenizer->tokens) );

	$res = $tokenizer->append($tokenizer->tokens[0]);
	Assert::same( 'hello world!hello', $tokenizer->reset()->joinAll() );
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->append(NULL);
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->append('');
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->append(array());
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->append(FALSE);
	Assert::same( 5, count($tokenizer->tokens) );
});


test(function() { // append with position
	$tokenizer = new MacroTokens('world');

	$res = $tokenizer->append('!', NULL);
	Assert::same( 'world!', $tokenizer->joinAll() );

	$res = $tokenizer->append('hello', 0);
	Assert::same( 'helloworld!', $tokenizer->reset()->joinAll() );

	$res = $tokenizer->append(' ', 1);
	Assert::same( 'hello world!', $tokenizer->reset()->joinAll() );

	$res = $tokenizer->append('*', -1);
	Assert::same( 'hello world*!', $tokenizer->reset()->joinAll() );

	$res = $tokenizer->append('false', FALSE);
	Assert::same( 'falsehello world*!', $tokenizer->reset()->joinAll() );
});


test(function() { // prepend
	$tokenizer = new MacroTokens('world!');

	$res = $tokenizer->prepend('hello ');
	Assert::same( $tokenizer, $res );
	Assert::same( 'hello world!', $tokenizer->joinAll() );
	Assert::same( 4, count($tokenizer->tokens) );

	$res = $tokenizer->prepend($tokenizer->tokens[2]);
	Assert::same( 'worldhello world!', $tokenizer->reset()->joinAll() );
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->prepend(NULL);
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->prepend('');
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->prepend(array());
	Assert::same( 5, count($tokenizer->tokens) );

	$res = $tokenizer->prepend(FALSE);
	Assert::same( 5, count($tokenizer->tokens) );
});
