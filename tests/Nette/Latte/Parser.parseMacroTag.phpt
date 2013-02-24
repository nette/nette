<?php

/**
 * Test: Nette\Latte\Parser::parseMacroTag().
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';



$parser = new Latte\Parser();


Assert::same( array('?', 'echo', ''), $parser->parseMacroTag('? echo') );
Assert::same( array('?', 'echo', ''), $parser->parseMacroTag('?echo') );
Assert::same( array('?', '', ''), $parser->parseMacroTag('?') );
Assert::same( array('=', '$var', '|escape'), $parser->parseMacroTag('$var') );
Assert::same( array('=', '$var', ''), $parser->parseMacroTag('!$var') );
Assert::same( array('=', '$var', ''), $parser->parseMacroTag('! $var') );
Assert::same( array('_', '"I love Nette"', ''), $parser->parseMacroTag('!_"I love Nette"') );
Assert::same( array('_', '$var', '|escape'), $parser->parseMacroTag('_$var') );
Assert::same( array('_', '$var', '|escape'), $parser->parseMacroTag('_ $var') );
Assert::same( array('_', '', '|escape'), $parser->parseMacroTag('_') );
Assert::same( array('/_', '', ''), $parser->parseMacroTag('/_') );
Assert::same( array('=', '$var', ''), $parser->parseMacroTag('!=$var') );
Assert::same( array('=', '$var', '|escape'), $parser->parseMacroTag('=$var') );
Assert::same( array('=', '$var', '|escape'), $parser->parseMacroTag('= $var') );
Assert::same( array('=', 'function()', '|escape'), $parser->parseMacroTag('function()') );
Assert::same( array('=', 'md5()', '|escape'), $parser->parseMacroTag('md5()') );
Assert::same( array('foo:bar', '', ''), $parser->parseMacroTag('foo:bar') );
Assert::same( array('=', ':bar', '|escape'), $parser->parseMacroTag(':bar') );
Assert::same( array('=', 'class::member', '|escape'), $parser->parseMacroTag('class::member') );
Assert::same( array('=', 'Namespace\Class::member()', '|escape'), $parser->parseMacroTag('Namespace\Class::member()') );
Assert::same( array('Link', '$var', ''), $parser->parseMacroTag('Link $var') );
Assert::same( array('link', '$var', ''), $parser->parseMacroTag('link $var') );
Assert::same( array('link', '$var', ''), $parser->parseMacroTag('link$var') );
Assert::same( array('block', '#name', ''), $parser->parseMacroTag('block #name') );
Assert::same( array('block', '#name', ''), $parser->parseMacroTag('block#name') );
Assert::same( array('/block', '', ''), $parser->parseMacroTag('/block') );
Assert::same( array('/block', '#name', ''), $parser->parseMacroTag('/block#name') );
Assert::same( array('/', '', ''), $parser->parseMacroTag('/') );
Assert::same( array('l', '', ''), $parser->parseMacroTag('l') );
Assert::same( array('=', '10', '|escape'), $parser->parseMacroTag('10') );
Assert::same( array('=', "'str'", '|escape'), $parser->parseMacroTag("'str'") );
Assert::same( array('=', '+10', '|escape'), $parser->parseMacroTag('+10') );
Assert::same( array('=', '-10', '|escape'), $parser->parseMacroTag('-10') );
