<?php

/**
 * Test: Latte\Tokenizer::getCoordinates
 *
 * @author     David Grudl
 */

use Latte\Tokenizer,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( array(1, 1), Tokenizer::getCoordinates("say \n123", 0));
Assert::same( array(1, 2), Tokenizer::getCoordinates("say \n123", 1));
Assert::same( array(1, 5), Tokenizer::getCoordinates("say \n123", 4));
Assert::same( array(2, 1), Tokenizer::getCoordinates("say \n123", 5));
