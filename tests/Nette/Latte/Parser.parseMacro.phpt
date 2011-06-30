<?php

/**
 * Test: Nette\Latte\Parser::parseMacro().
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$parser = new Latte\Parser();


Assert::equal( array('?', 'echo', ''), $parser->parseMacro('? echo') );
Assert::equal( array('?', 'echo', ''), $parser->parseMacro('?echo') );
Assert::equal( array('?', '', ''), $parser->parseMacro('?') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacro('$var') );
Assert::equal( array('=', '$var', ''), $parser->parseMacro('!$var') );
Assert::equal( array('=', '$var', ''), $parser->parseMacro('! $var') );
Assert::equal( array('_', '"I love Nette"', ''), $parser->parseMacro('!_"I love Nette"') );
Assert::equal( array('_', '$var', '|escape'), $parser->parseMacro('_$var') );
Assert::equal( array('_', '$var', '|escape'), $parser->parseMacro('_ $var') );
Assert::equal( array('=', '$var', ''), $parser->parseMacro('!=$var') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacro('=$var') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacro('= $var') );
Assert::equal( array('=', 'function()', '|escape'), $parser->parseMacro('function()') );
Assert::equal( array('foo:bar', '', ''), $parser->parseMacro('foo:bar') );
Assert::equal( array('=', ':bar', '|escape'), $parser->parseMacro(':bar') );
Assert::equal( array('=', 'class::member', '|escape'), $parser->parseMacro('class::member') );
Assert::equal( array('Link', '$var', ''), $parser->parseMacro('Link $var') );
Assert::equal( array('link', '$var', ''), $parser->parseMacro('link $var') );
Assert::equal( array('link', '$var', ''), $parser->parseMacro('link$var') );
Assert::equal( array('block', '#name', ''), $parser->parseMacro('block #name') );
Assert::equal( array('block', '#name', ''), $parser->parseMacro('block#name') );
Assert::equal( array('/block', '', ''), $parser->parseMacro('/block') );
Assert::equal( array('/block', '#name', ''), $parser->parseMacro('/block#name') );
Assert::equal( array('l', '', ''), $parser->parseMacro('l') );
Assert::equal( array('=', '10', '|escape'), $parser->parseMacro('10') );
Assert::equal( array('=', "'str'", '|escape'), $parser->parseMacro("'str'") );
Assert::equal( array('=', '+10', '|escape'), $parser->parseMacro('+10') );
Assert::equal( array('=', '-10', '|escape'), $parser->parseMacro('-10') );
