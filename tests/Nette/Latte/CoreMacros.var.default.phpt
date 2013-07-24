<?php

/**
 * Test: Nette\Latte\CoreMacros: {var ...} {default ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\CoreMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);

test(function() use ($compiler) { // {var ... }
	Assert::same( '<?php $var = \'hello\' ?>',  $compiler->expandMacro('var', 'var => hello', '')->openingCode );
	Assert::same( '<?php $var = 123 ?>',  $compiler->expandMacro('var', '$var => 123', '')->openingCode );
	Assert::same( '<?php $var = 123 ?>',  $compiler->expandMacro('var', '$var = 123', '')->openingCode );
	Assert::same( '<?php $var = 123 ?>',  $compiler->expandMacro('var', '$var => 123', 'filter')->openingCode );
	Assert::same( '<?php $var1 = 123; $var2 = "nette framework" ?>',  $compiler->expandMacro('var', 'var1 = 123, $var2 => "nette framework"', '')->openingCode );
	Assert::same( '<?php $temp->var1 = 123 ?>',  $compiler->expandMacro('var', '$temp->var1 = 123', '')->openingCode );

	Assert::exception(function() use ($compiler) {
		$compiler->expandMacro('var', '$var => "123', '');
	}, 'Nette\Utils\TokenizerException', 'Unexpected %a% on line 1, column 9.');
});


test(function() use ($compiler) { // {default ...}
	Assert::same( "<?php extract(array('var' => 'hello'), EXTR_SKIP) ?>",  $compiler->expandMacro('default', 'var => hello', '')->openingCode );
	Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  $compiler->expandMacro('default', '$var => 123', '')->openingCode );
	Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  $compiler->expandMacro('default', '$var = 123', '')->openingCode );
	Assert::same( "<?php extract(array('var' => 123), EXTR_SKIP) ?>",  $compiler->expandMacro('default', '$var => 123', 'filter')->openingCode );
	Assert::same( "<?php extract(array('var1' => 123, 'var2' => \"nette framework\"), EXTR_SKIP) ?>",  $compiler->expandMacro('default', 'var1 = 123, $var2 => "nette framework"', '')->openingCode );

	Assert::exception(function() use ($compiler) {
		$compiler->expandMacro('default', '$temp->var1 = 123', '');
	}, 'Nette\Latte\CompileException', "Unexpected '->' in {default \$temp->var1 = 123}");
});
