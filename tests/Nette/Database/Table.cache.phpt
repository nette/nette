<?php

/**
 * Test: Nette\Database\Table: Caching.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$connection->setCacheStorage(new Nette\Caching\Storages\FileStorage(TEMP_DIR));
$connection->getCache()->clean();



$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT * FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$book = $bookSelection->fetch();
$book->title;
$book->translator;
$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT `id`, `title`, `translator_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$book = $bookSelection->fetch();
$book->author_id;
Assert::same('SELECT * FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT `id`, `title`, `translator_id`, `author_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());
