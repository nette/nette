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

$book = $connection->table('book');

$book1 = $book->get(1);
Assert::equal('Jakub Vrana', $book1->author->name);



$book2 = $book->insert(array(
	'author_id' => $author->getPrimary(),
	'title' => 'Game of Thrones',
));

Assert::equal('Tyrion Lannister', $book2->author->name);




$book2->author_id = $connection->table('author')->get(11);
$book2->update();

Assert::equal('Jakub Vrana', $book2->author->name);




$tag = $connection->table('tag')->insert(array(
	'name' => 'PC Game',
));

$tag->name = 'Xbox Game';
$tag->update();


$bookTag = $book2->related('book_tag')->insert(array(
	'tag_id' => $tag,
));


$app = $connection->table('book')->get(5);
$tags = iterator_to_array($app->related('book_tag'));
Assert::equal('Xbox Game', reset($tags)->tag->name);
