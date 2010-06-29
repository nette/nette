<?php

/**
 * Test: Nette\NeonParser simple values.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\NeonParser;



require __DIR__ . '/../initialize.php';



$parser = new NeonParser;

T::dump( $parser->parse('') );

T::dump( $parser->parse('   ') );

T::dump( $parser->parse('1') );

T::dump( $parser->parse('-1.2') );

T::dump( $parser->parse('-1.2e2') );

T::dump( $parser->parse('true') );

T::dump( $parser->parse('null') );

T::dump( $parser->parse('the"string#literal') );

T::dump( $parser->parse('the"string #literal') );

T::dump( $parser->parse('"the\'string #literal"') );

T::dump( $parser->parse("'the\"string #literal'") );

T::dump( $parser->parse("''") );

T::dump( $parser->parse('""') );

T::dump( $parser->parse('x') );

T::dump( $parser->parse("\nx\n") );

T::dump( $parser->parse("\n  x  \n") );

T::dump( $parser->parse("  x") );



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
