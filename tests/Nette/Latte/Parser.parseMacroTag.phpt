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


Assert::same( array('?', 'echo', '', FALSE), $parser->parseMacroTag('? echo') );
Assert::same( array('?', 'echo', '', FALSE), $parser->parseMacroTag('?echo') );
Assert::same( array('?', 'echo', '', TRUE), $parser->parseMacroTag('?echo/') );
Assert::same( array('?', '', '', FALSE), $parser->parseMacroTag('?') );
Assert::same( array('?', '', '', TRUE), $parser->parseMacroTag('?/') );
Assert::same( array('?', '', '', TRUE), $parser->parseMacroTag('? /') );
Assert::same( array('?', '/', '', FALSE), $parser->parseMacroTag('? / ') );
Assert::same( array('=', '$var', '|escape', FALSE), $parser->parseMacroTag('$var') );
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('!$var') );
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('! $var') );
Assert::same( array('_', '"I love Nette"', '', FALSE), $parser->parseMacroTag('!_"I love Nette"') );
Assert::same( array('_', '$var', '|escape', FALSE), $parser->parseMacroTag('_$var') );
Assert::same( array('_', '$var', '|escape', FALSE), $parser->parseMacroTag('_ $var') );
Assert::same( array('_', '', '|escape', FALSE), $parser->parseMacroTag('_') );
Assert::same( array('/_', '', '', FALSE), $parser->parseMacroTag('/_') );
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('!=$var') );
Assert::same( array('=', '$var', '|escape', FALSE), $parser->parseMacroTag('=$var') );
Assert::same( array('=', '$var', '|escape', FALSE), $parser->parseMacroTag('= $var') );
Assert::same( array('=', 'function()', '|escape', FALSE), $parser->parseMacroTag('function()') );
Assert::same( array('=', 'md5()', '|escape', FALSE), $parser->parseMacroTag('md5()') );
Assert::same( array('foo:bar', '', '', FALSE), $parser->parseMacroTag('foo:bar') );
Assert::same( array('=', ':bar', '|escape', FALSE), $parser->parseMacroTag(':bar') );
Assert::same( array('=', 'class::member', '|escape', FALSE), $parser->parseMacroTag('class::member') );
Assert::same( array('=', 'Namespace\Class::member()', '|escape', FALSE), $parser->parseMacroTag('Namespace\Class::member()') );
Assert::same( array('Link', '$var', '', FALSE), $parser->parseMacroTag('Link $var') );
Assert::same( array('link', '$var', '', FALSE), $parser->parseMacroTag('link $var') );
Assert::same( array('link', '$var', '', FALSE), $parser->parseMacroTag('link$var') );
Assert::same( array('block', '#name', '', FALSE), $parser->parseMacroTag('block #name') );
Assert::same( array('block', '#name', '', FALSE), $parser->parseMacroTag('block#name') );
Assert::same( array('/block', '', '', FALSE), $parser->parseMacroTag('/block') );
Assert::same( array('/block', '#name', '', FALSE), $parser->parseMacroTag('/block#name') );
Assert::same( array('/', '', '', FALSE), $parser->parseMacroTag('/') );
Assert::same( array('l', '', '', FALSE), $parser->parseMacroTag('l') );
Assert::same( array('=', '10', '|escape', FALSE), $parser->parseMacroTag('10') );
Assert::same( array('=', "'str'", '|escape', FALSE), $parser->parseMacroTag("'str'") );
Assert::same( array('=', '+10', '|escape', FALSE), $parser->parseMacroTag('+10') );
Assert::same( array('=', '-10', '|escape', FALSE), $parser->parseMacroTag('-10') );
