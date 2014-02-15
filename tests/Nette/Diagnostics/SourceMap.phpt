<?php

/**
 * Test: Nette\Diagnostics\Helper::sourceMapLookup()
 *
 * @author     Jan Dolecek
 * @package    Nette\Diagnostics
 * @subpackage UnitTests
 */

use Nette\Diagnostics\SourceMapHelper;
use Tester\Assert;



require __DIR__ . '/../bootstrap.php';


$file = __DIR__ . '/SourceMap.phtml';
Assert::same( array('SourceMap.phpt', 1), SourceMapHelper::sourceMapLookup($file, 15) );
Assert::same( array('SourceMap.phpt', 15), SourceMapHelper::sourceMapLookup($file, 16) );
Assert::same( array('SourceMap.phpt', 15), SourceMapHelper::sourceMapLookup($file, 17) );
Assert::same( NULL, SourceMapHelper::sourceMapLookup($file, 19) );

// Another source map within the same file - not supported at the moment
// Assert::same( array('SourceMap.phpt', 1), Helpers::sourceMapLookup($file, 32) );
