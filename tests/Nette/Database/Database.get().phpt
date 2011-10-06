<?php

/**
 * Test: Nette\Database Single row detail.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$application = $connection->table('application')->get(1);
$data = array(
	'id' => 1,
	'author_id' => 11,
	'maintainer_id' => 11,
	'title' => 'Adminer',
	'web' => 'http://www.adminer.org/',
	'slogan' => 'Database management in single PHP file',
);

Assert::equal($data, iterator_to_array($application));
