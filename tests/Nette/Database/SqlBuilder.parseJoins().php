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
Assert::equal('WHERE priorit.id IS NULL', $query);
Assert::equal(
	'LEFT JOIN nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
	'LEFT JOIN ntopics AS topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
	'LEFT JOIN npriorities AS priorit ON topic.nPriorityId = priorit.nPriorityId',
	trim($join)
);
