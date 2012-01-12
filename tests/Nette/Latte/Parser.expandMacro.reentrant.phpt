<?php

/**
 * Test: Nette\Latte\Compiler::expandMacro() and reentrant.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
$set = new Latte\Macros\MacroSet($compiler);
$set->addMacro('test', 'echo %node.word', 'echo %node.word');

list($node, $open) = $compiler->expandMacro('test', 'first second', '');
Assert::same( '<?php echo "first" ?>',  $open );
Assert::same( '<?php echo "first" ?>',  $node->close('') );
