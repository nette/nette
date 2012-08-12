<?php

/**
 * Test: Nette\Latte\Parser::parseMacroTag().
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';



$parser = new Latte\Parser();


Assert::equal( array('?', 'echo', ''), $parser->parseMacroTag('? echo') );
Assert::equal( array('?', 'echo', ''), $parser->parseMacroTag('?echo') );
Assert::equal( array('?', '', ''), $parser->parseMacroTag('?') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacroTag('$var') );
Assert::equal( array('=', '$var', ''), $parser->parseMacroTag('!$var') );
Assert::equal( array('=', '$var', ''), $parser->parseMacroTag('! $var') );
Assert::equal( array('_', '"I love Nette"', ''), $parser->parseMacroTag('!_"I love Nette"') );
Assert::equal( array('_', '$var', '|escape'), $parser->parseMacroTag('_$var') );
Assert::equal( array('_', '$var', '|escape'), $parser->parseMacroTag('_ $var') );
Assert::equal( array('_', '', '|escape'), $parser->parseMacroTag('_') );
Assert::equal( array('/_', '', ''), $parser->parseMacroTag('/_') );
Assert::equal( array('=', '$var', ''), $parser->parseMacroTag('!=$var') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacroTag('=$var') );
Assert::equal( array('=', '$var', '|escape'), $parser->parseMacroTag('= $var') );
Assert::equal( array('=', 'function()', '|escape'), $parser->parseMacroTag('function()') );
Assert::equal( array('=', 'md5()', '|escape'), $parser->parseMacroTag('md5()') );
Assert::equal( array('foo:bar', '', ''), $parser->parseMacroTag('foo:bar') );
Assert::equal( array('=', ':bar', '|escape'), $parser->parseMacroTag(':bar') );
Assert::equal( array('=', 'class::member', '|escape'), $parser->parseMacroTag('class::member') );
Assert::equal( array('Link', '$var', ''), $parser->parseMacroTag('Link $var') );
Assert::equal( array('link', '$var', ''), $parser->parseMacroTag('link $var') );
Assert::equal( array('link', '$var', ''), $parser->parseMacroTag('link$var') );
Assert::equal( array('block', '#name', ''), $parser->parseMacroTag('block #name') );
Assert::equal( array('block', '#name', ''), $parser->parseMacroTag('block#name') );
Assert::equal( array('/block', '', ''), $parser->parseMacroTag('/block') );
Assert::equal( array('/block', '#name', ''), $parser->parseMacroTag('/block#name') );
Assert::equal( array('/', '', ''), $parser->parseMacroTag('/') );
Assert::equal( array('l', '', ''), $parser->parseMacroTag('l') );
Assert::equal( array('=', '10', '|escape'), $parser->parseMacroTag('10') );
Assert::equal( array('=', "'str'", '|escape'), $parser->parseMacroTag("'str'") );
Assert::equal( array('=', '+10', '|escape'), $parser->parseMacroTag('+10') );
Assert::equal( array('=', '-10', '|escape'), $parser->parseMacroTag('-10') );
