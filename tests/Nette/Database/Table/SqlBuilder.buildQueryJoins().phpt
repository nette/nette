<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: parseJoins().
 *
 * @author     Svaťa Šimara
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test2.sql");


class SqlBuilderMock extends SqlBuilder
{
	public function parseJoins(& $joins, & $query, $inner = FALSE)
	{
		parent::parseJoins($joins, $query);
	}
	public function buildQueryJoins(array $joins, $leftConditions = array())
	{
		return parent::buildQueryJoins($joins, $leftConditions);
	}
	public function buildLeftJoinConditions($allLeftJoinConditions) {
		return parent::buildLeftJoinConditions($allLeftJoinConditions);
	}
}

$reflection = new DiscoveredReflection($connection);
$sqlBuilder = new SqlBuilderMock('nUsers', $connection, $reflection);

$joins = array();
$leftJoin = ':nusers_ntopics.topic.priorit.id IS NOT NULL, :nusers_ntopics.topic.priorit.id = ?';
$sqlBuilder->parseJoins($joins, $leftJoin);
$leftConditions = $sqlBuilder->buildLeftJoinConditions($leftJoin);
$join = $sqlBuilder->buildQueryJoins($joins, $leftConditions);
Assert::same('priorit.id IS NOT NULL AND priorit.id = ?', $leftConditions['priorit']);

$tables = $connection->getSupplementalDriver()->getTables();
if (!in_array($tables[0]['name'], array('npriorities', 'ntopics', 'nusers', 'nusers_ntopics', 'nusers_ntopics_alt'), TRUE)) {
	Assert::same(
		'LEFT JOIN nUsers_nTopics AS nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN nTopics AS topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN nPriorities AS priorit ON topic.nPriorityId = priorit.nPriorityId AND (priorit.id IS NOT NULL AND priorit.id = ?)',
		trim($join)
	);
} else {
	Assert::same(
		'LEFT JOIN nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN ntopics AS topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN npriorities AS priorit ON topic.nPriorityId = priorit.nPriorityId AND (priorit.id IS NOT NULL AND priorit.id = ?)',
		trim($join)
	);
}



Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

$sqlBuilder = new SqlBuilderMock('author', $connection, $reflection);

$joins = array();
$leftJoin = ':book(translator).next_volume = ? OR :book(translator).next_volume IS NULL';
$sqlBuilder->parseJoins($joins, $leftJoin);
$leftConditions = $sqlBuilder->buildLeftJoinConditions($leftJoin);
$join = $sqlBuilder->buildQueryJoins($joins, $leftConditions);
Assert::same('book.next_volume = ? OR book.next_volume IS NULL', $leftConditions['book']);
Assert::same(
	'LEFT JOIN book ON author.id = book.translator_id AND (book.next_volume = ? OR book.next_volume IS NULL)',
	trim($join)
);
