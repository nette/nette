<?php

/**
 * Test: NetteTestHelpers::dump() basic types & output()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/../initialize.php';



dump( 10 );

dump( 20.2 );

dump( TRUE );

dump( FALSE );

dump( NULL );

dump( 'hello' );

dump( (object) NULL );

dump( array() );

dump( fopen(__FILE__, 'r') );

dump( array(10, 20.2, TRUE, FALSE, NULL, 'hello', (object) NULL, array(), fopen(__FILE__, 'r')) );


class TestClass
{
	public $x = array(10, NULL);

	private $y = 'hello';

	protected $z = 30;
}

dump( new TestClass );

output('message');

output();

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