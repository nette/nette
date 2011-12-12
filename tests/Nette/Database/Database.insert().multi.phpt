<?php

/**
 * Test: Nette\Database Multi insert operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$connection->table('author')->insert(array(
	array(
		'name' => 'Catelyn Stark',
		'web' => 'http://example.com',
		'born' => new DateTime('2011-11-11'),
	),
	array(
		'name' => 'Sansa Stark',
		'web' => 'http://example.com',
		'born' => new DateTime('2021-11-11'),
	),
));



$connection->table('application_tag')->where('application_id', 1)->delete();
$connection->table('application')->get(1)->related('application_tag')->insert(array(
	array('tag_id' => 21),
	array('tag_id' => 22),
	array('tag_id' => 23),
));
