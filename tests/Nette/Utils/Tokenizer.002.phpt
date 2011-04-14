<?php

/**
 * Test: Nette\Utils\Tokenizer::tokenize with names
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Tokenizer;



require __DIR__ . '/../bootstrap.php';



$tokenizer = new Tokenizer(array(
	'number' => '\d+',
	'whitespace' => '\s+',
	'string' => '\w+',
));
$tokenizer->tokenize("say \n123");
Assert::same( array(
	array('say', 'string'),
	array(" \n", 'whitespace'),
	array('123', 'number'),
), $tokenizer->tokens );

try {
	$tokenizer->tokenize('say 123;');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.", $e );
}
