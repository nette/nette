<?php

/**
 * Test: Nette\Database\Table\Selection: Page
 *
 * @author     s4muel
 * @dataProvider? ../databases.ini
 */
use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

//public function page($page, $itemsPerPage, & $numOfPages = NULL)

test(function() use ($context) { //first page, one item per page
	$numberOfPages = 0;

	$tags = $context->table('tag')->page(1, 1, $numOfPages);
	Assert::equal(1, count($tags)); //one item on first page
	Assert::equal(4, $numOfPages); //four pages total

	//calling the same without the $numOfPages reference
	unset($tags);
	$tags = $context->table('tag')->page(1, 1);
	Assert::equal(1, count($tags)); //one item on first page
});

test(function() use ($context) { //second page, three items per page
	$numberOfPages = 0;

	$tags = $context->table('tag')->page(2, 3, $numOfPages);
	Assert::equal(1, count($tags)); //one item on second page
	Assert::equal(2, $numOfPages); //two pages total

	//calling the same without the $numOfPages reference
	unset($tags);
	$tags = $context->table('tag')->page(2, 3);
	Assert::equal(1, count($tags)); //one item on second page
});

test(function() use ($context) { //page with no items
	$tags = $context->table('tag')->page(10, 4);
	Assert::equal(0, count($tags)); //one item on second page
});

test(function() use ($context) { //page with no items (page not in range)
	$tags = $context->table('tag')->page(100, 4);
	Assert::equal(0, count($tags)); //one item on second page
});

test(function() use ($context) { //less items than $itemsPerPage
	$tags = $context->table('tag')->page(1, 100);
	Assert::equal(4, count($tags)); //all four items from db
});

test(function() use ($context) { //invalid params
	$tags = $context->table('tag')->page('foo', 'bar');
	Assert::equal(0, count($tags)); //no items
});
