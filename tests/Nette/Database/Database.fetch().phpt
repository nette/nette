<?php

/**
 * Test: Nette\Database Find one item by URL.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$tags = array();
$application = $connection->table('application')->where('title', 'Adminer')->fetch();
foreach ($application->related('application_tag')->where('tag_id', 21) as $application_tag) {
	$tags[] = $application_tag->tag->name;
}

Assert::equal(array('PHP'), $tags);
