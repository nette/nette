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

// Inject source-map into comments
$lines = explode("\n", $compiled); $linesOriginal = explode("\n", $template->getSource());
foreach ($map as $item) {
	list($compiledLineNo, $originalLineNo) = $item;

//	echo "$compiledLineNo -> $originalLineNo\n";
//	echo "O:  " . @$linesOriginal[$originalLineNo - 1] . "\n";
//	echo "C:  " . @$lines[$compiledLineNo] . "\n";
//	echo "\n\n";
	echo "\tarray("; var_export($originalLineNo); echo ', '; var_export(trim(@$linesOriginal[$originalLineNo - 1])); echo ', '; var_export(trim($lines[$compiledLineNo])); echo "),\n";


	if ( ! isset($lines[$compiledLineNo])) {
		/*Assert::fail*/echo("Source map contains mapping from line $compiledLineNo, which however doesn't exist");
	}
	else {
		$lines[$compiledLineNo] .= "    {* original-line $originalLineNo *}";
	}
}
$annotated = implode("\n", $lines);

file_put_contents(__DIR__ . '/templates/sourceMaps2.phtml', $annotated);


exit;


// Validate mapping
$code = $expected;
Assert::true(preg_match_all('~\s*\{\* original-line (\d+) \*\}\h*$~m', $code, $matches, PREG_OFFSET_CAPTURE) > 0);
foreach ($matches[1] as $match) {
	$originalLineNo = intval($match[0]);
	$offset = $match[1];
	$compiledLineNo = substr_count($code, "\n", 0, $offset) + 1; // number of line feeds + 1 for the first line

	$found = in_array(array($compiledLineNo, $originalLineNo), $map);

	//echo "$found: $compiledLineNo -> $originalLineNo\n";
	if (!$found) {
		echo "Missing mapping from compiled $compiledLineNo -> original $originalLineNo\n";
	}
}
