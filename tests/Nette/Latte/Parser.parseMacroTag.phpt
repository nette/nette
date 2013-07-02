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
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('$var') );
Assert::same( array('=', '$var', '|noescape', FALSE), $parser->parseMacroTag('!$var') );
Assert::same( array('=', '$var', '|noescape', FALSE), $parser->parseMacroTag('! $var') );
Assert::same( array('_', '"I love Nette"', '|noescape', FALSE), $parser->parseMacroTag('!_"I love Nette"') );
Assert::same( array('_', '$var', '', FALSE), $parser->parseMacroTag('_$var') );
Assert::same( array('_', '$var', '', FALSE), $parser->parseMacroTag('_ $var') );
Assert::same( array('_', '', '', FALSE), $parser->parseMacroTag('_') );
Assert::same( array('/_', '', '', FALSE), $parser->parseMacroTag('/_') );
Assert::same( array('=', '$var', '|noescape', FALSE), $parser->parseMacroTag('!=$var') );
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('=$var') );
Assert::same( array('=', '$var', '', FALSE), $parser->parseMacroTag('= $var') );
Assert::same( array('=', 'function()', '', FALSE), $parser->parseMacroTag('function()') );
Assert::same( array('=', 'md5()', '', FALSE), $parser->parseMacroTag('md5()') );
Assert::same( array('foo:bar', '', '', FALSE), $parser->parseMacroTag('foo:bar') );
Assert::same( array('=', ':bar', '', FALSE), $parser->parseMacroTag(':bar') );
Assert::same( array('=', 'class::member', '', FALSE), $parser->parseMacroTag('class::member') );
Assert::same( array('=', 'Namespace\Class::member()', '', FALSE), $parser->parseMacroTag('Namespace\Class::member()') );
Assert::same( array('Link', '$var', '', FALSE), $parser->parseMacroTag('Link $var') );
Assert::same( array('link', '$var', '', FALSE), $parser->parseMacroTag('link $var') );
Assert::same( array('link', '$var', '', FALSE), $parser->parseMacroTag('link$var') );
Assert::same( array('block', '#name', '', FALSE), $parser->parseMacroTag('block #name') );
Assert::same( array('block', '#name', '', FALSE), $parser->parseMacroTag('block#name') );
Assert::same( array('/block', '', '', FALSE), $parser->parseMacroTag('/block') );
Assert::same( array('/block', '#name', '', FALSE), $parser->parseMacroTag('/block#name') );
Assert::same( array('/', '', '', FALSE), $parser->parseMacroTag('/') );
Assert::same( array('l', '', '', FALSE), $parser->parseMacroTag('l') );
Assert::same( array('=', '10', '', FALSE), $parser->parseMacroTag('10') );
Assert::same( array('=', "'str'", '', FALSE), $parser->parseMacroTag("'str'") );
Assert::same( array('=', '+10', '', FALSE), $parser->parseMacroTag('+10') );
Assert::same( array('=', '-10', '', FALSE), $parser->parseMacroTag('-10') );

Assert::same( array('=', '$var', "|mod:'\\':a:b:c':arg2 |mod2:|mod3", FALSE), $parser->parseMacroTag("\$var |mod:'\\':a:b:c':arg2 |mod2:|mod3") );
Assert::same( array('=', '$var', '|mod|mod2|noescape', FALSE), $parser->parseMacroTag('!$var|mod|mod2') );
