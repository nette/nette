<?php

/**
 * Test: Nette\Utils\Tokenizer::tokenize simple
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Tokenizer;



require __DIR__ . '/../bootstrap.php';



$tokenizer = new Tokenizer(array(
	'\d+',
	'\s+',
	'\w+',
));
$tokenizer->tokenize('say 123');
Assert::same( array('say', ' ', '123'), $tokenizer->tokens );

Assert::exception(function() use ($tokenizer) {
	$tokenizer->tokenize('say 123;');
}, 'Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.");
