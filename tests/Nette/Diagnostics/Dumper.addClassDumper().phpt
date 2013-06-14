<?php

/**
 * Test: Nette\Diagnostics\Dumper::addClassDumper()
 *
 * @author     Filip Procházka
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Dumper;



require __DIR__ . '/../bootstrap.php';



class ClassDumper_Test
{
	public $x = array(10, NULL);

	private $y = 'hello';

	protected $z = 30.0;
}

class Connection
{

	public $host;

	public $database;


	public function __construct($host, $database)
	{
		$this->host = $host;
		$this->database = $database;
	}

}

function ClassDumper_Test_dumper (ClassDumper_Test $test) {
	return $test->x;
}

Dumper::addClassDumper('ClassDumper_Test', 'ClassDumper_Test_dumper');

function ClassDumper_Connection_dumper(Connection $db) {
	return $db->host . ':' . $db->database;
}

Dumper::addClassDumper('Connection', 'ClassDumper_Connection_dumper');

Assert::match( 'ClassDumper_Test (2)
   0 => 10
   1 => NULL
', Dumper::toText(new ClassDumper_Test) );

Assert::match('Connection => "localhost:nette" (15)', Dumper::toText(new Connection('localhost', 'nette')));


function ClassDumper_Connection_bad_dumper(Connection $db) {
	return $db; // returns object
}

Dumper::addClassDumper('Connection', 'ClassDumper_Connection_bad_dumper');

Assert::exception(function () {
	Dumper::toText(new Connection('localhost', 'nette'));
}, 'Nette\UnexpectedValueException', 'Callback ClassDumper_Connection_bad_dumper must return scalar or array, object returned.');
