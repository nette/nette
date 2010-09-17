<?php

/**
 * Test: Nette\NeonParser simple values.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\NeonParser;



require __DIR__ . '/../bootstrap.php';



$parser = new NeonParser;

Assert::null( $parser->parse('') );
Assert::null( $parser->parse('   ') );
Assert::same( 1, $parser->parse('1') );
Assert::same( -1.2, $parser->parse('-1.2') );
Assert::same( -120.0, $parser->parse('-1.2e2') );
Assert::true( $parser->parse('true') );
Assert::null( $parser->parse('null') );
Assert::same( 'the"string#literal', $parser->parse('the"string#literal') );
Assert::same( 'the"string', $parser->parse('the"string #literal') );
Assert::same( "the'string #literal", $parser->parse('"the\'string #literal"') );
Assert::same( 'the"string #literal', $parser->parse("'the\"string #literal'") );
Assert::same( "", $parser->parse("''") );
Assert::same( "", $parser->parse('""') );
Assert::same( 'x', $parser->parse('x') );
Assert::same( "x", $parser->parse("\nx\n") );
Assert::same( "x", $parser->parse("\n  x  \n") );
Assert::same( "x", $parser->parse("  x") );
