<?php

/**
 * Test: Nette\Database\Table\Selection: Row class
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



Nette\Database\Table\Selection::$defaultRowClass = 'TestRow';

class TestRow extends Nette\Database\Table\ActiveRow
{
}

class Test2Row extends Nette\Database\Table\ActiveRow
{

	public function &__get($key)
	{
		return parent::__get(preg_replace('~^test_~', '', $key));
	}

	public function __isset($key)
	{
		return parent::__isset(preg_replace('~^test_~', '', $key));
	}

}



$book = $connection->table('book')->setRowClass('Test2Row')->get(2);


Assert::same('JUSH', $book->test_title);
Assert::same('Jakub Vrana', $book->author->test_name);



$tags = $book->related('book_tag');
foreach ($tags as $tag) { // JUSH has just one tag
	Assert::same('JavaScript', $tag->tag->test_name);
}



Assert::same('TestRow', $connection->table('book')->get(1)->getReflection()->name);
