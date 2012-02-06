<?php

/**
 * Test: Nette\Database\Table: Basic operations with DiscoveredReflection.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



require_once __DIR__ . '/connect.inc.php';
$connection->setDatabaseReflection(new Database\Reflection\DiscoveredReflection);



$appTags = array();
foreach ($connection->table('book') as $book) {
	$appTags[$book->title] = array(
		'author' => $book->author->name,
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {
		$appTags[$book->title]['tags'][] = $book_tag->tag->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('PHP', 'MySQL'),
	),
	'JUSH' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('JavaScript'),
	),
	'Nette' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP'),
	),
	'Dibi' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP', 'MySQL'),
	),
), $appTags);




$book = $connection->table('book')->get(1);
Assert::same('Jakub Vrana', $book->translator->name);



$book = $connection->table('book')->get(2);
Assert::true(isset($book->author_id));
Assert::false(empty($book->author_id));

Assert::false(isset($book->translator_id));
Assert::true(empty($book->translator_id));
Assert::false(isset($book->test));

Assert::false(isset($book->author));
Assert::false(isset($book->translator));
Assert::true(empty($book->author));
Assert::true(empty($book->translator));



$connection->query('ALTER TABLE `book` ADD COLUMN `next_volume` int NULL AFTER `title`;');
$connection->query('ALTER TABLE `book` ADD CONSTRAINT `book_volume` FOREIGN KEY (`next_volume`) REFERENCES `book` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
$connection->query('UPDATE `book` SET `next_volume` = 3 WHERE `id` = 4');

$book = $connection->table('book')->get(4);
Assert::same('Nette', $book->volume->title);
Assert::same('Nette', $book->ref('book', 'next_volume')->title);
