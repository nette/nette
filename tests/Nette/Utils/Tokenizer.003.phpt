<?php

/**
 * Test: Nette\Utils\Tokenizer::fetch()
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
Assert::false( $tokenizer->fetch('s') );
Assert::same( 'say', $tokenizer->fetch('say') );
Assert::same( ' ', $tokenizer->fetch() );



$tokenizer = new Tokenizer(array(
	'number' => '\d+',
	'whitespace' => '\s+',
	'string' => '\w+',
));
$tokenizer->tokenize("say 123");
Assert::false( $tokenizer->fetch('say') );
Assert::same( 'say', $tokenizer->fetch('string') );
Assert::same( ' ', $tokenizer->fetch() );
