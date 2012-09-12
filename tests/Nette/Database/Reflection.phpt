<?php

/**
 * Test: Nette\Database\Connection: reflection for MySQL
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$driver = $connection->getSupplementalDriver();
$tables = $driver->getTables();
$tables = array_filter($tables, function($t) { return in_array($t['name'], array('author', 'book', 'book_tag', 'tag')); });
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

$expectedColumns = array(
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
);

switch ($driverName) {
	case 'pgsql':
		$expectedColumns[0]['nativetype'] = 'INT4';
		$expectedColumns[0]['size'] = 32;
		$expectedColumns[0]['default'] = "nextval('author_id_seq'::regclass)";
		break;
}

Assert::same($expectedColumns , $columns);



$indexes = $driver->getIndexes('book_tag');
switch ($driverName) {
	case 'pgsql':
		$expectedIndexes = array(
			array(
				'name' => 'book_tag_pkey',
				'unique' => TRUE,
				'primary' => TRUE,
				'columns' => array(
					'book_id',
					'tag_id',
				),
			),
		);
		break;
	case 'mysql':
	default:
		$expectedIndexes = array(
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
		);
		break;
}

Assert::same($expectedIndexes, $indexes);



$reflection = new Nette\Database\Reflection\DiscoveredReflection;
$reflection->setConnection($connection);

// test caching primary key in table with multiple primary keys
Assert::same(NULL, $reflection->getPrimary('book_tag'));
Assert::same(NULL, $reflection->getPrimary('book_tag'));
