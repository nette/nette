<?php

/**
 * Test: Nette\Latte\Engine: parsing of undefined macros doen't throw anything
 *
 * @author     Pavel Ptacek based on David Grudl's work (pretty much copy-paste)
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';

// pre-proccess the template string
$content = file_get_contents(__DIR__ . '/templates/general.latte');
$content .= file_get_contents(__DIR__ . '/templates/undefined.macros.latte');

// Prepare the only macro functions
$tagStart = function(Latte\MacroNode $node, $writer) {
	return '?><b><?php ';
};
$tagEnd = function(Latte\MacroNode $node, $writer) {
	return '?></b><?php ';
};

// Prepare the parser engine
$parser = new Latte\Parser;
$parser->ignoreUndefinedMacros = true;
$set = new Latte\Macros\MacroSet($parser);
$set->addMacro('shouldBeParsedIntoB', $tagStart, $tagEnd);

// Run test
$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.html"), $parser->parse($content));
