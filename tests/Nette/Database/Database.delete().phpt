<?php

/**
 * Test: Nette\Database Delete operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$connection->table('application_tag')->where('application_id', 4)->delete();

$count = $connection->table('application_tag')->where('application_id', 4)->count();
Assert::equal(0, $count);



$application = $connection->table('application')->get(3);
$application->related('application_tag')->delete();

$count = $connection->table('application_tag')->where('application_id', 3)->count();
Assert::equal(0, $count);



$application->delete();
Assert::equal(0, count($connection->table('application')->find(3)));
