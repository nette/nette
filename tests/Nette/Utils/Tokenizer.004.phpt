<?php

/**
 * Test: Nette\Utils\Tokenizer traversing
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
$tokenizer->tokenize('say 123');

Assert::false( $tokenizer->fetchAll('number') );
Assert::same( 'say ', $tokenizer->fetchUntil('number') );
Assert::true( $tokenizer->isCurrent('whitespace') );
Assert::true( $tokenizer->isNext('number') );
Assert::true( $tokenizer->isNext('string', 'number') );
Assert::false( $tokenizer->fetchUntil('string', 'number', 'whitespace') );
Assert::same( '123', $tokenizer->fetchAll() );
