<?php

/**
 * Test: Nette\Utils\Tokenizer traversing
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
$tokenizer->tokenize('say 123');

Assert::false( $tokenizer->hasPrev() );
Assert::true( $tokenizer->hasNext() );
Assert::false( $tokenizer->fetchAll(T_DNUMBER) );
Assert::same( 'say ', $tokenizer->fetchUntil(T_DNUMBER) );
Assert::true( $tokenizer->isCurrent(T_WHITESPACE) );
Assert::true( $tokenizer->hasPrev() );
Assert::true( $tokenizer->hasNext() );
Assert::true( $tokenizer->isPrev(T_STRING) );
Assert::false( $tokenizer->isPrev(T_DNUMBER) );
Assert::true( $tokenizer->isNext(T_DNUMBER) );
Assert::true( $tokenizer->isNext(T_STRING, T_DNUMBER) );
Assert::false( $tokenizer->fetchUntil(T_STRING, T_DNUMBER, T_WHITESPACE) );
Assert::same( '123', $tokenizer->fetchAll() );
Assert::true( $tokenizer->hasPrev() );
Assert::false( $tokenizer->hasNext() );
