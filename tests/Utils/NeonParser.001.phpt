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

1

-1.2

-120.0

TRUE

NULL

"the"string#literal"

"the"string"

"the'string #literal"

"the"string #literal"

""

""

"x"

"x"

"x"

"x"
