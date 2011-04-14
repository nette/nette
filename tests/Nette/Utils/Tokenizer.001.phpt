<?php

/**
 * Test: Nette\Utils\Tokenizer::tokenize simple
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
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

try {
	$tokenizer->tokenize('say 123;');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.", $e );
}
