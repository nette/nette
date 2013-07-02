<?php

/**
 * Test: Nette\Utils\TokenIterator traversing
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Tokenizer,
	Nette\Utils\TokenIterator;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$tokenizer = new Tokenizer(array(
		T_DNUMBER => '\d+',
		T_WHITESPACE => '\s+',
		T_STRING => '\w+',
	));
	$traverser = new TokenIterator($tokenizer->tokenize('say 123'));
	$traverser->ignored[] = T_WHITESPACE;

	Assert::same( -1, $traverser->position );
	Assert::same( 'say', $traverser->nextValue() );
	Assert::same( 0, $traverser->position );

	$traverser->position = -1;
	Assert::null( $traverser->nextValue(T_DNUMBER) );
	Assert::same( -1, $traverser->position );
	Assert::same( 'say', $traverser->nextValue(T_STRING) );
	Assert::same( 0, $traverser->position );

	$traverser->position = -1;
	Assert::same( '', $traverser->joinAll(T_DNUMBER) );
	Assert::same( -1, $traverser->position );
	Assert::same( 'say', $traverser->joinAll(T_STRING) );
	Assert::same( 0, $traverser->position );

	$traverser->position = -1;
	Assert::same( '', $traverser->joinUntil(T_STRING) );
	Assert::same( -1, $traverser->position );
	Assert::same( 'say', $traverser->joinUntil(T_WHITESPACE) );
	Assert::same( 0, $traverser->position );

	$traverser->position = -1;
	Assert::same( -1, $traverser->position );
	Assert::same( 'say ', $traverser->joinUntil(T_DNUMBER) );
	Assert::same( 1, $traverser->position );


	$traverser->position = 0;
	Assert::null( $traverser->nextValue(T_STRING) );
	Assert::same( 0, $traverser->position );
	Assert::same( '123', $traverser->nextValue(T_STRING, T_DNUMBER) );
	Assert::same( 2, $traverser->position );

	$traverser->position = 0;
	Assert::same( '', $traverser->joinAll(T_STRING) );
	Assert::same( 0, $traverser->position );
	Assert::same( '123', $traverser->joinAll(T_STRING, T_DNUMBER) );
	Assert::same( 2, $traverser->position );

	$traverser->position = 0;
	Assert::same( '', $traverser->joinUntil(T_WHITESPACE) );
	Assert::same( 0, $traverser->position );
	Assert::same( ' ', $traverser->joinUntil(T_STRING, T_DNUMBER) );
	Assert::same( 1, $traverser->position );


	$traverser->position = 2;
	Assert::null( $traverser->nextValue() );
	Assert::same( 3, $traverser->position );

	$traverser->position = 2;
	Assert::null( $traverser->nextValue() );
	Assert::null( $traverser->nextValue(T_STRING, T_DNUMBER, T_WHITESPACE) );
	Assert::same( 3, $traverser->position );

	$traverser->position = 2;
	Assert::same( '', $traverser->joinAll() );
	Assert::same( '', $traverser->joinAll(T_STRING, T_DNUMBER, T_WHITESPACE) );
	Assert::same( 3, $traverser->position );

	$traverser->position = 2;
	Assert::same( '', $traverser->joinUntil(T_STRING, T_DNUMBER, T_WHITESPACE) );
	Assert::same( 2, $traverser->position );
});


test(function() {
	$tokenizer = new Tokenizer(array(
		'\d+',
		'\s+',
		'\w+',
	));
	$traverser = new TokenIterator($tokenizer->tokenize('say 123'));
	Assert::null( $traverser->nextValue('s') );
	Assert::same( 'say', $traverser->nextValue('say') );
	Assert::same( ' ', $traverser->nextValue() );
});
