<?php

/**
 * Test: Nette\Database\Table: Join.
 *
 * @author     Svaťa Šimara
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	$sql = $context->table('book')->left('translator.name', 'Geek')->select('book.*')->getSql();
	Assert::same(reformat('SELECT [book].* FROM [book] LEFT JOIN [author] AS [translator] ON [book].[translator_id] = [translator].[id] AND ([translator].[name] = ?)'), $sql);
});

test(function() use ($context){
	$sql = $context->table('book')->left(':product_price.active', 1)->select('book.*, :product_price.value')->getSql();
	Assert::same(reformat('SELECT [book].*, [product_price].[value] FROM [book] LEFT JOIN [product_price] ON [book].[id] = [product_price].[book_id] AND ([product_price].[active] = ?)'), $sql);
});

test(function() use ($context){
	$sql = $context->table('book')
		->left(':product_price.active', 1)
		->where(':book_tag.tag.name LIKE', 'PHP')
		->left(':product_price.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL')
		->select('book.*, :product_price.value')->getSql();
	Assert::same(reformat(
		'SELECT [book].*, [product_price].[value] FROM [book]'
		. ' LEFT JOIN [book_tag] ON [book].[id] = [book_tag].[book_id] AND ([book_tag].[tag_id] IS NOT NULL)'
		. ' LEFT JOIN [tag] ON [book_tag].[tag_id] = [tag].[id]'
		. ' LEFT JOIN [product_price] ON [book].[id] = [product_price].[book_id] AND ([product_price].[active] = ? AND [product_price].[value] > ?)'
		. ' WHERE ([tag].[name] LIKE ?)'), $sql);
});