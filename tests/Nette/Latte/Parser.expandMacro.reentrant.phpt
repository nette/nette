<?php

/**
 * Test: Nette\Latte\Compiler::expandMacro() and reentrant.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
$set = new Latte\Macros\MacroSet($compiler);
$set->addMacro('test', 'echo %node.word', 'echo %node.word');

$node = $compiler->expandMacro('test', 'first second', '');
Assert::same( '<?php echo "first" ?>',  $node->openingCode );
$node->macro->nodeClosed($node);
Assert::same( '<?php echo "first" ?>',  $node->closingCode );
