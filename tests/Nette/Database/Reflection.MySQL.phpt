<?php

/**
 * Test: Nette\Database\Connection: reflection for MySQL
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$driver = $connection->getSupplementalDriver();
$tables = $driver->getTables();
usort($tables, function($a, $b) { return strcmp($a['name'], $b['name']); });

Assert::same( array(
	array('name' => 'author', 'view' => FALSE),
	array('name' => 'book', 'view' => FALSE),
	array('name' => 'book_tag', 'view' => FALSE),
	array('name' => 'tag', 'view' => FALSE),
), $tables );



$columns = $driver->getColumns('author');
array_walk($columns, function(& $item) {
	Assert::true( is_array($item['vendor']) );
	unset($item['vendor']);
});

Assert::same( array(
	array(
		'name' => 'id',
		'table' => 'author',
		'nativetype' => 'INT',
		'size' => 11,
		'unsigned' => FALSE,
		'nullable' => FALSE,
		'default' => NULL,
		'autoincrement' => TRUE,
		'primary' => TRUE,
	),
	array(
		'name' => 'name',
		'table' => 'author',
		'nativetype' => 'VARCHAR',
		'size' => 30,
		'unsigned' => FALSE,
		'nullable' => FALSE,
		'default' => NULL,
		'autoincrement' => FALSE,
		'primary' => FALSE,
	),
	array(
		'name' => 'web',
		'table' => 'author',
		'nativetype' => 'VARCHAR',
		'size' => 100,
		'unsigned' => FALSE,
		'nullable' => FALSE,
		'default' => NULL,
		'autoincrement' => FALSE,
		'primary' => FALSE,
	),
	array(
		'name' => 'born',
		'table' => 'author',
		'nativetype' => 'DATE',
		'size' => NULL,
		'unsigned' => FALSE,
		'nullable' => TRUE,
		'default' => NULL,
		'autoincrement' => FALSE,
		'primary' => FALSE,
	),
), $columns );



Assert::same( array(
	array(
		'name' => 'PRIMARY',
		'unique' => TRUE,
		'primary' => TRUE,
		'columns' => array(
			'book_id',
			'tag_id',
		),
	),
	array(
		'name' => 'book_tag_tag',
		'unique' => FALSE,
		'primary' => FALSE,
		'columns' => array(
			'tag_id',
		),
	),
), $driver->getIndexes('book_tag') );
