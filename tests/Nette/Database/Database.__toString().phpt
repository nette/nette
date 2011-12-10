<?php

/**
 * Test: Nette\Database Calling __toString().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



Assert::same('2', (string) $connection->table('application')->get(2));



Assert::same(2, $connection->table('application')->get(2)->getPrimary());



Assert::throws(function() use ($connection) {
	$appTag = $connection->table('application_tag')->where('application_id', 1)->where('tag_id', 21)->fetch();
	$appTag->getPrimary();
}, 'Nette\\NotSupportedException', 'Table application_tag does not have any primary key.');
