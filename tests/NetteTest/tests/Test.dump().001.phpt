<?php

/**
 * Test: TestHelpers::dump() basic types & TestHelpers::note()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



TestHelpers::dump( 10 );

TestHelpers::dump( 20.2 );

TestHelpers::dump( TRUE );

TestHelpers::dump( FALSE );

TestHelpers::dump( NULL );

TestHelpers::dump( 'hello' );

TestHelpers::dump( (object) NULL );

TestHelpers::dump( array() );

TestHelpers::dump( fopen(__FILE__, 'r') );

TestHelpers::dump( array(10, 20.2, TRUE, FALSE, NULL, 'hello', (object) NULL, array(), fopen(__FILE__, 'r')) );


class TestClass
{
	public $x = array(10, NULL);

	private $y = 'hello';

	protected $z = 30;
}

TestHelpers::dump( new TestClass );

TestHelpers::note('message');

TestHelpers::note();

echo 'EOF';



__halt_compiler() ?>

------EXPECT------
int(10)

float(20.2)

bool(TRUE)

bool(FALSE)

NULL

string(5) "hello"

object(stdClass) (0) {}

array(0)

resource of type(stream)

array(9) {
	0 => int(10)
	1 => float(20.2)
	2 => bool(TRUE)
	3 => bool(FALSE)
	4 => NULL
	5 => string(5) "hello"
	6 => object(stdClass) (0) {}
	7 => array(0)
	8 => resource of type(stream)
}

object(TestClass) (3) {
	"x" => array(2) {
		0 => int(10)
		1 => NULL
	}
	"y" private => string(5) "hello"
	"z" protected => int(30)
}

message

===

EOF