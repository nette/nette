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
$tokenizer->tokenize("say \n123");
Assert::same( array(
	array('value' => 'say', 'type' => T_STRING, 'offset' => 0),
	array('value' => " \n", 'type' => T_WHITESPACE, 'offset' => 3),
	array('value' => '123', 'type' => T_DNUMBER, 'offset' => 5),
), $tokenizer->tokens );

Assert::exception(function() use ($tokenizer) {
	$tokenizer->tokenize('say 123;');
}, 'Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.");
