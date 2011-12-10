<?php

/**
 * Test: Nette\Database Update operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$author = $connection->table('author')->get(12);
$author->name = 'Tyrion Lannister';
$author->update();

$application = $connection->table('application');

$application1 = $application->get(1);
Assert::equal('Jakub Vrana', $application1->author->name);



$application2 = $application->insert(array(
	'author_id' => $author->getPrimary(),
	'title' => 'Game of Thrones',
));

Assert::equal('Tyrion Lannister', $application2->author->name);




$application2->author_id = $connection->table('author')->get(11);
$application2->update();

Assert::equal('Jakub Vrana', $application2->author->name);




$tag = $connection->table('tag')->insert(array(
	'name' => 'PC Game',
));

$tag->name = 'Xbox Game';
$tag->update();


$applicationTag = $application2->related('application_tag')->insert(array(
	'tag_id' => $tag,
));


$app = $connection->table('application')->get(5);
$tags = iterator_to_array($app->related('application_tag'));
Assert::equal('Xbox Game', reset($tags)->tag->name);
