<?php

/**
 * Test: Source maps for Latte
 *
 * @author     Jan Dolecek
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';


$expected = file_get_contents(__DIR__ . '/templates/sourceMaps.phtml');

$template = new FileTemplate(__DIR__ . '/templates/sourceMaps.latte');
$template->registerFilter(new Latte\Engine);
$compiled = $template->compile();


// Extract source map
$beforeMap = strpos($compiled, '//sourceMap[');
$hash = substr($compiled, $beforeMap + 12, 40);
$len = substr($compiled, $beforeMap + 12 + 40 + 1, 6);
$map = substr($compiled, $beforeMap + 12 + 40 + 1 + 6 + 1, $len);
$map = unserialize($map);


$linesCompiled = explode("\n", $compiled);
$linesOriginal = explode("\n", $template->getSource());

// Expected mapping generator (see $expectedMapping below)
/*
foreach ($map as $item) {
	list($compiledLineNo, $originalLineNo) = $item;
	echo "\tarray("; var_export($originalLineNo); echo ', '; var_export(trim(@$linesOriginal[$originalLineNo - 1])); echo ', '; var_export(trim(@$linesCompiled[$compiledLineNo])); echo "),\n";
}*/


// source line, source code, compiled code
$expectedMapping = array(
	array(12, '<h1 n:block="title">Page {$name}</h1>', '?><h1>Page <?php echo Nette\\Templating\\Helpers::escapeHtml($name, ENT_NOQUOTES) ?></h1>'),
	array(14, '{$x}', '<?php echo Nette\\Templating\\Helpers::escapeHtml($x, ENT_NOQUOTES) ?>'),
	array(24, '</li>', '<?php $iterations = 0; foreach ($items as $item): ?>	<li>'),
	array(18, 'Item {$item->id}:', 'Item <?php echo Nette\\Templating\\Helpers::escapeHtml($item->id, ENT_NOQUOTES) ?>:'),
	array(21, 'onmouseover="console.log(this)">', '<a'),
	array(24, '</li>', '<?php $iterations++; endforeach ?>'),
	array(31, '{/form}', '<?php Nette\\Latte\\Macros\\FormMacros::renderFormBegin($form = $_form = (is_object("myForm") ? "myForm" : $_control["myForm"]), array()) ?>'),
	array(28, 'Name: {input name}<br>', 'Name: <?php echo $_form["name"]->getControl()->addAttributes(array()) ?><br />'),
	array(29, 'Mail: {input email}<br>', 'Mail: <?php echo $_form["email"]->getControl()->addAttributes(array()) ?><br />'),
	array(30, '{input add}', '<?php echo $_form["add"]->getControl()->addAttributes(array()) ?>'),
	array(31, '{/form}', '<?php Nette\\Latte\\Macros\\FormMacros::renderFormEnd($_form) ?>'),
);


foreach ($expectedMapping as $mapping) {
	list ($originalLineNo, $expectedSourceLine, $expectedCompiledLine) = $mapping;

	Assert::same($expectedSourceLine, trim($linesOriginal[$originalLineNo - 1]));

	$found = FALSE;
	$compiledLine = NULL;
	foreach ($map as $mapItem) {
		if ($mapItem[1] === $originalLineNo) {
			$compiledLine = $linesCompiled[$mapItem[0]];
			if ($expectedCompiledLine === trim($compiledLine)) {
				$found = TRUE;
			} else {
				// no problem
				//echo "found not matching src line $originalLineNo:\nO:   $expectedSourceLine\nE:   $expectedCompiledLine\nA:   $compiledLine\n\n\n";
			}
		}
	}
	if ( ! $found) {
		Assert::fail("Missing compiled line '$expectedCompiledLine' for source line $originalLineNo '$expectedSourceLine'");
	}
}
