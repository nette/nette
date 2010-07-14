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
10

20.2

TRUE

FALSE

NULL

"hello"

stdClass()

array()

stream resource

array(
	10
	20.2
	TRUE
	FALSE
	NULL
	"hello"
	stdClass()
	array()
	stream resource
)

TestClass(
	"x" => array(
		10
		NULL
	)
	"y" private => "hello"
	"z" protected => 30
)

message

===

EOF