<?php

/**
 * Test: Nette\Utils\Tokenizer::tokenize with names
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Tokenizer;


require __DIR__ . '/../bootstrap.php';


$tokenizer = new Tokenizer(array(
	T_DNUMBER => '\d+',
	T_WHITESPACE => '\s+',
	T_STRING => '\w+',
));
$tokens = $tokenizer->tokenize("say \n123");
Assert::same( array(
	array(Tokenizer::VALUE => 'say', Tokenizer::OFFSET => 0, Tokenizer::TYPE => T_STRING),
	array(Tokenizer::VALUE => " \n", Tokenizer::OFFSET => 3, Tokenizer::TYPE => T_WHITESPACE),
	array(Tokenizer::VALUE => '123', Tokenizer::OFFSET => 5, Tokenizer::TYPE => T_DNUMBER),
), $tokens );

Assert::exception(function() use ($tokenizer) {
	$tokenizer->tokenize('say 123;');
}, 'Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.");
