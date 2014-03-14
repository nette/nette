<?php

/**
 * Test: Nette\Latte\Runtime\CachingIterator basic usage.
 *
 * @author     David Grudl
 */

use Nette\Latte\Runtime\CachingIterator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() { // ==> Two items in array

	$arr = array('Nette', 'Framework');

	$iterator = new CachingIterator($arr);
	$iterator->rewind();
	Assert::true( $iterator->valid() );
	Assert::true( $iterator->isFirst() );
	Assert::false( $iterator->isLast() );
	Assert::same( 1, $iterator->getCounter() );

	$iterator->next();
	Assert::true( $iterator->valid() );
	Assert::false( $iterator->isFirst() );
	Assert::true( $iterator->isLast() );
	Assert::same( 2, $iterator->getCounter() );

	$iterator->next();
	Assert::false( $iterator->valid() );

	$iterator->rewind();
	Assert::true( $iterator->isFirst() );
	Assert::false( $iterator->isLast() );
	Assert::same( 1, $iterator->getCounter() );
	Assert::false( $iterator->isEmpty() );
});


test(function() {
	$arr = array('Nette');

	$iterator = new CachingIterator($arr);
	$iterator->rewind();
	Assert::true( $iterator->valid() );
	Assert::true( $iterator->isFirst() );
	Assert::true( $iterator->isLast() );
	Assert::same( 1, $iterator->getCounter() );

	$iterator->next();
	Assert::false( $iterator->valid() );

	$iterator->rewind();
	Assert::true( $iterator->isFirst() );
	Assert::true( $iterator->isLast() );
	Assert::same( 1, $iterator->getCounter() );
	Assert::false( $iterator->isEmpty() );
});


test(function() {
	$arr = array();

	$iterator = new CachingIterator($arr);
	$iterator->next();
	$iterator->next();
	Assert::false( $iterator->isFirst() );
	Assert::true( $iterator->isLast() );
	Assert::same( 0, $iterator->getCounter() );
	Assert::true( $iterator->isEmpty() );
});
