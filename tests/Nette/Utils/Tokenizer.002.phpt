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
	array('value' => 'say', 'type' => 'string', 'line' => 1),
	array('value' => " \n", 'type' => 'whitespace', 'line' => 1),
	array('value' => '123', 'type' => 'number', 'line' => 2),
), $tokenizer->tokens );

try {
	$tokenizer->tokenize('say 123;');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Utils\TokenizerException', "Unexpected ';' on line 1, column 8.", $e );
}
