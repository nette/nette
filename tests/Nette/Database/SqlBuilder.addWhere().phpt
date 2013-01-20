<?php

/**
 * Test: Nette\Database\Table\SqlBuilder::addWhere()
 *
 * @author     Miloslav Hůla
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$sqlBuilder = new Nette\Database\Table\SqlBuilder($connection->table('foo'));
Assert::true( $sqlBuilder->addWhere('id', 1) );
Assert::false( $sqlBuilder->addWhere('id', 1) );
Assert::true( $sqlBuilder->addWhere('id', 2) );



$sqlBuilder = new Nette\Database\Table\SqlBuilder($connection->table('foo'));
$params = array();

$sqlBuilder->addWhere('id1', $params[] = 1);
$sqlBuilder->addWhere('NOT id1', $params[] = 1);

$sqlBuilder->addWhere('id2', NULL);
$sqlBuilder->addWhere('NOT id2', NULL);

$sqlBuilder->addWhere('id3', $params[] = array(1, 2));
$sqlBuilder->addWhere('NOT id3', $params[] = array(1, 2));

$sqlBuilder->addWhere('id4', array());
$sqlBuilder->addWhere('NOT id4', array());

$sqlBuilder->addWhere('id5', $params[] = array(NULL));
$sqlBuilder->addWhere('NOT id5', $params[] = array(NULL));

$sql = $sqlBuilder->buildSelectQuery();
switch ($driverName) {
	case 'pgsql':
		Assert::same( 'SELECT * FROM "foo" WHERE ("id1" = ?) AND (NOT "id1" = ?) AND ("id2" IS NULL) AND (NOT "id2" IS NULL) AND ("id3" IN (?)) AND (NOT "id3" IN (?)) AND (FALSE) AND (TRUE) AND ("id5" IN (?)) AND (NOT "id5" IN (?))', $sql );
		break;

	case 'mysql':
	default:
		Assert::same( 'SELECT * FROM `foo` WHERE (`id1` = ?) AND (NOT `id1` = ?) AND (`id2` IS NULL) AND (NOT `id2` IS NULL) AND (`id3` IN (?)) AND (NOT `id3` IN (?)) AND (0) AND (1) AND (`id5` IN (?)) AND (NOT `id5` IN (?))', $sql );
		break;
}

Assert::same($params, $sqlBuilder->getParameters());



$selection = new Nette\Database\Table\Selection($connection, 'author', new Nette\Database\Reflection\DiscoveredReflection($connection));
$sqlBuilder = new Nette\Database\Table\SqlBuilder($connection->table('book'));

$sqlBuilder->addWhere('author_id', $selection);
$sqlBuilder->addWhere('NOT author_id', $selection);
$sql = $sqlBuilder->buildSelectQuery();
switch ($driverName) {
	case 'pgsql':
		Assert::same( 'SELECT * FROM "book" WHERE ("author_id" IN (SELECT "id" FROM "author")) AND (NOT "author_id" IN (SELECT "id" FROM "author"))', $sql );
		break;

	case 'mysql':
	default:
		Assert::same( 'SELECT * FROM `book` WHERE (`author_id` IN (?, ?)) AND (NOT `author_id` IN (?, ?))', $sql );
		break;
}



// ensure empty subquery result
$connection->query('DELETE FROM book');
$connection->query('DELETE FROM author');

$selection = new Nette\Database\Table\Selection($connection, 'author', new Nette\Database\Reflection\DiscoveredReflection($connection));
$sqlBuilder = new Nette\Database\Table\SqlBuilder($connection->table('book'));

$sqlBuilder->addWhere('author_id', $selection);
$sqlBuilder->addWhere('NOT author_id', $selection);
$sql = $sqlBuilder->buildSelectQuery();
switch ($driverName) {
	case 'pgsql':
		Assert::same( 'SELECT * FROM "book" WHERE ("author_id" IN (SELECT "id" FROM "author")) AND (NOT "author_id" IN (SELECT "id" FROM "author"))', $sql );
		break;

	case 'mysql':
	default:
		Assert::same( 'SELECT * FROM `book` WHERE (0) AND (1)', $sql );
		break;
}
