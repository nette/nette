<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: parseJoins().
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test2.sql");

use Tester\Assert;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;


class SqlBuilderMock extends SqlBuilder
{
	public function parseJoins(& $joins, & $query, $inner = FALSE)
	{
		parent::parseJoins($joins, $query);
	}
	public function buildQueryJoins(array $joins)
	{
		return parent::buildQueryJoins($joins);
	}
}

$reflection = new DiscoveredReflection($connection);
$sqlBuilder = new SqlBuilderMock('nUsers', $connection, $reflection);


$joins = array();
$query = 'WHERE :nusers_ntopics.topic.priorit.id IS NULL';
$sqlBuilder->parseJoins($joins, $query);
$join = $sqlBuilder->buildQueryJoins($joins);
Assert::same('WHERE priorit.id IS NULL', $query);

$tables = $connection->getSupplementalDriver()->getTables();
if (!in_array($tables[0]['name'], array('npriorities', 'ntopics', 'nusers', 'nusers_ntopics', 'nusers_ntopics_alt'), TRUE)) {
	Assert::same(
		'LEFT JOIN nUsers_nTopics AS nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN nTopics AS topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN nPriorities AS priorit ON topic.nPriorityId = priorit.nPriorityId',
		trim($join)
	);
} else {
	Assert::same(
		'LEFT JOIN nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN ntopics AS topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN npriorities AS priorit ON topic.nPriorityId = priorit.nPriorityId',
		trim($join)
	);
}


Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");

$sqlBuilder = new SqlBuilderMock('author', $connection, $reflection);

$joins = array();
$query = 'WHERE :book(translator).next_volume IS NULL';
$sqlBuilder->parseJoins($joins, $query);
$join = $sqlBuilder->buildQueryJoins($joins);
Assert::same('WHERE book.next_volume IS NULL', $query);
Assert::same(
	'LEFT JOIN book ON author.id = book.translator_id',
	trim($join)
);
