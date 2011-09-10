<?php

/**
 * Test: Nette\Latte\Macros\UIMacros {ifset #block}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\UIMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
UIMacros::install($parser);
function item1($a) { return $a[1]; }

// {ifset ... }
Assert::same( '<?php if (isset($_l->blocks["block"])): ?>',  item1($parser->expandMacro('ifset', '#block')) );
Assert::same( '<?php if (isset($item->var["#test"], $_l->blocks["block"])): ?>',  item1($parser->expandMacro('ifset', '$item->var["#test"], #block')) );

Assert::throws(function() use ($parser) {
	Assert::same( '<?php if (isset($var)): ?>',  item1($parser->expandMacro('ifset', '$var')) );
}, 'Nette\Latte\ParseException', 'Unhandled macro {ifset}');
