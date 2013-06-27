<?php

/**
 * Test: Nette\Utils\Tokenizer::fetch()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Tokenizer;



require __DIR__ . '/../bootstrap.php';



test(function() {
	$tokenizer = new Tokenizer(array(
		'\d+',
		'\s+',
		'\w+',
	));
	$tokenizer->tokenize('say 123');
	Assert::false( $tokenizer->fetch('s') );
	Assert::same( 'say', $tokenizer->fetch('say') );
	Assert::same( ' ', $tokenizer->fetch() );
});



test(function() {
	$tokenizer = new Tokenizer(array(
		T_DNUMBER => '\d+',
		T_WHITESPACE => '\s+',
		T_STRING => '\w+',
	));
	$tokenizer->tokenize("say 123");
	Assert::same( Tokenizer::createToken('say', T_STRING, 0), $tokenizer->fetchToken('say') );
	Assert::same( ' ', $tokenizer->fetch() );
});
