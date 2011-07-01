<?php

/**
 * Test: Nette\Latte\CoreMacros: {var ...} {default ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
CoreMacros::install($parser);
function item1($a) { return $a[1]; }

// {var ... }
Assert::same( '<?php $var = \'hello\' ?>',  item1($parser->expandMacro('var', 'var => hello', '')) );
Assert::same( '<?php $var = 123 ?>',  item1($parser->expandMacro('var', '$var => 123', '')) );
Assert::same( '<?php $var = 123 ?>',  item1($parser->expandMacro('var', '$var = 123', '')) );
Assert::same( '<?php $var = 123 ?>',  item1($parser->expandMacro('var', '$var => 123', 'filter')) );
Assert::same( '<?php $var1 = 123; $var2 = "nette framework" ?>',  item1($parser->expandMacro('var', 'var1 = 123, $var2 => "nette framework"', '')) );
Assert::same( '<?php $temp->var1 = 123 ?>',  item1($parser->expandMacro('var', '$temp->var1 = 123', '')) );

Assert::throws(function() use ($parser) {
	item1($parser->expandMacro('var', '$var => "123', ''));
}, 'Nette\Utils\TokenizerException', 'Unexpected %a% on line 1, column 9.');


// {default ...}
Assert::same( "<?php extract(array('var' => 'hello'), EXTR_SKIP) ?>",  item1($parser->expandMacro('default', 'var => hello', '')) );
Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  item1($parser->expandMacro('default', '$var => 123', '')) );
Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  item1($parser->expandMacro('default', '$var = 123', '')) );
Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  item1($parser->expandMacro('default', '$var => 123', 'filter')) );
Assert::same( "<?php extract(array('var1' => 123, 'var2' => \"nette framework\"), EXTR_SKIP) ?>",  item1($parser->expandMacro('default', 'var1 = 123, $var2 => "nette framework"', '')) );

Assert::throws(function() use ($parser) {
	item1($parser->expandMacro('default', '$temp->var1 = 123', ''));
}, 'Nette\Latte\ParseException', "Unexpected '-' in {default \$temp->var1 = 123}");
