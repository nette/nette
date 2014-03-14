<?php

/**
 * Test: CompileExceptions have correct line number in mixed php/latte template
 *
 * @author     Jan Dolecek, David Grudl
 */

use Nette\Latte,
	Nette\Templating\Template,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Template;
$template->registerFilter(new Latte\Engine);

try {
	$template->setSource(
	'<?php
	 // php block
	?>
	{notDefined line 4}
	')->compile();
} catch(\Nette\Latte\CompileException $e) {
	Assert::same(4, $e->sourceLine);
	Assert::same("Unknown macro {notDefined}", $e->getMessage());
}

try {
	$template->setSource(
	'{*
	*}
	<?xml ?>
	{notDefined line 4}
	')->compile();
} catch(\Nette\Latte\CompileException $e) {
	Assert::same(4, $e->sourceLine);
	Assert::same("Unknown macro {notDefined}", $e->getMessage());
}
