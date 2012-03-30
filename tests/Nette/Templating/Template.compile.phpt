<?php

/**
 * Test: Nette\Templating\Template::compile() to preserve line numbers
 *
 * @author     Jan Dolecek
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Templating\Helpers;



require __DIR__ . '/../bootstrap.php';


/**
 * Validate if line numbers in code match. Fail if not so.
 * @param string $code
 */
function validateLineNumbers($code) {
	if (preg_match_all('~Line (\d+)\b~', $code, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches[1] as $match) {
			list ($lineNo, $offset) = $match;
			$actualLineNo = substr_count($code, "\n", 0, $offset) + 1; // number of line feeds + 1 for the first line

			if ($actualLineNo != $lineNo) { // intentionally != because $lineNo is extracted from text
				Assert::fail("Source line of code '$lineNo' has moved to '$actualLineNo'.");
			}
		}
	}
}


$path = __DIR__ . '/templates/lineNumbers.latte';
$tpl = new \Nette\Templating\FileTemplate($path);
$tpl->registerFilter(function($code) {
	validateLineNumbers($code);
	return $code;
});

$compiled = $tpl->compile();
Assert::same(file_get_contents($path . '.phtml'), $compiled);
validateLineNumbers($compiled);
