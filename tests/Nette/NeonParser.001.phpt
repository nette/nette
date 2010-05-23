<?php

/**
 * Test: Nette\NeonParser simple values.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\NeonParser;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$parser = new NeonParser;

dump( $parser->parse('') );

dump( $parser->parse('   ') );

dump( $parser->parse('1') );

dump( $parser->parse('-1.2') );

dump( $parser->parse('-1.2e2') );

dump( $parser->parse('true') );

dump( $parser->parse('null') );

dump( $parser->parse('the"string#literal') );

dump( $parser->parse('the"string #literal') );

dump( $parser->parse('"the\'string #literal"') );

dump( $parser->parse("'the\"string #literal'") );

dump( $parser->parse("''") );

dump( $parser->parse('""') );

dump( $parser->parse('x') );

dump( $parser->parse("\nx\n") );

dump( $parser->parse("\n  x  \n") );

dump( $parser->parse("  x") );



__halt_compiler() ?>

------EXPECT------
NULL

NULL

int(1)

float(-1.2)

float(-120)

bool(TRUE)

NULL

string(18) "the"string#literal"

string(10) "the"string"

string(19) "the'string #literal"

string(19) "the"string #literal"

string(0) ""

string(0) ""

string(1) "x"

string(1) "x"

string(1) "x"

string(1) "x"
