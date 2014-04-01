<?php

/**
 * Test: CompileExceptions have correct line number in mixed php/latte template
 *
 * @author     Jan Dolecek, David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

try {
	$latte->compile(
	'<?php
	 // php block
	?>
	{notDefined line 4}
	');
} catch(\Latte\CompileException $e) {
	Assert::same(4, $e->sourceLine);
	Assert::same("Unknown macro {notDefined}", $e->getMessage());
}

try {
	$latte->compile(
	'{*
	*}
	<?xml ?>
	{notDefined line 4}
	');
} catch(\Latte\CompileException $e) {
	Assert::same(4, $e->sourceLine);
	Assert::same("Unknown macro {notDefined}", $e->getMessage());
}
