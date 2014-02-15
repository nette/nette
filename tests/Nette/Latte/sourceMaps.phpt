<?php

/**
 * Test: Compile map creation
 *
 * Check that compiled latte looks fine,
 * and that generated lines map correctly to original ones (using comments in expected file)
 *
 * @author     Jan Dolecek
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte;
use Nette\Templating\FileTemplate;
use Tester\Assert;



require __DIR__ . '/../bootstrap.php';


$expected = file_get_contents(__DIR__ . '/expected/sourceMaps.phtml');

$template = new FileTemplate(__DIR__ . '/templates/sourceMaps.latte');
$template->registerFilter(new Latte\Engine);
$compiled = $template->compile();
Assert::match(preg_replace('~\s*\{\*.+\*\}\h*$~m', '', $expected), $compiled);


// Extract source map
preg_match('~// source map (\w+): ([^\s]+)~', $compiled, $match);
$map = json_decode($match[2], TRUE);


// Validate mapping
$code = $expected;
Assert::true(preg_match_all('~\s*\{\* original-line (\d+) \*\}\h*$~m', $code, $matches, PREG_OFFSET_CAPTURE) > 0);
foreach ($matches[1] as $match) {
	$originalLineNo = intval($match[0]);
	$offset = $match[1];
	$compiledLineNo = substr_count($code, "\n", 0, $offset) + 1; // number of line feeds + 1 for the first line

	if (!isset($map[$compiledLineNo])) {
		echo "Missing recors in sourceMap for compiled line $compiledLineNo\n";

	} elseif (!in_array($originalLineNo, $map[$compiledLineNo])) {
		echo "Missing mapping from compiled $compiledLineNo -> original $originalLineNo (found -> " . implode(', ', $map[$compiledLineNo]) . ")\n";

	}
}
