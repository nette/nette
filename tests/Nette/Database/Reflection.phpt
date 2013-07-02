<?php

/**
 * Test: Nette\Database\Connection: reflection
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


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
	case 'mysql':
		break;
	case 'pgsql':
		$expectedColumns[0]['nativetype'] = 'INT4';
		$expectedColumns[0]['default'] = "nextval('author_id_seq'::regclass)";
		$expectedColumns[0]['size'] = NULL;
		$expectedColumns[1]['size'] = NULL;
		$expectedColumns[2]['size'] = NULL;
		break;
	case 'sqlite':
		$expectedColumns[0]['nativetype'] = 'INTEGER';
		$expectedColumns[0]['size'] = NULL;
		$expectedColumns[1]['nativetype'] = 'TEXT';
		$expectedColumns[1]['size'] = NULL;
		$expectedColumns[2]['nativetype'] = 'TEXT';
		$expectedColumns[2]['size'] = NULL;
		break;
	case 'sqlsrv':
		$expectedColumns[0]['size'] = NULL;
		$expectedColumns[1]['size'] = NULL;
		$expectedColumns[2]['size'] = NULL;
		break;
	default:
		Assert::fail("Unsupported driver $driverName");
}

Assert::same($expectedColumns, $columns);


$indexes = $driver->getIndexes('book_tag');
switch ($driverName) {
	case 'pgsql':
		Assert::same( array(
			array(
				'name' => 'book_tag_pkey',
				'unique' => TRUE,
				'primary' => TRUE,
				'columns' => array(
					'book_id',
					'tag_id',
				),
			),
		), $indexes);
		break;
	case 'sqlite':
		Assert::same( array(
			array(
				'name' => 'sqlite_autoindex_book_tag_1',
				'unique' => TRUE,
				'primary' => TRUE,
				'columns' => array(
					'book_id',
					'tag_id',
				),
			),
		), $indexes);
		break;
	case 'sqlsrv':
		Assert::same( array(
			array(
				'name' => 'PK_book_tag',
				'unique' => TRUE,
				'primary' => TRUE,
				'columns' => array(
					'book_id',
					'tag_id',
				),
			),
		), $indexes);
		break;
	case 'mysql':
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
		), $indexes);
		break;
	default:
		Assert::fail("Unsupported driver $driverName");
}


$reflection = new Nette\Database\Reflection\DiscoveredReflection($connection);

$primary = $reflection->getPrimary('book_tag');
Assert::same(array('book_id', 'tag_id'), $primary);
