<?php

/**
 * Test: Nette\Utils\Finder result test.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Finder;



require __DIR__ . '/../bootstrap.php';



// check key => value pair
$finder = Finder::findFiles(basename(__FILE__))->in(__DIR__);

$arr = iterator_to_array($finder);
Assert::same(1, count($arr));
Assert::true(isset($arr[__FILE__]));
Assert::true($arr[__FILE__] instanceof SplFileInfo);
Assert::same(__FILE__, (string) $arr[__FILE__]);


// missing in() & from()
$finder = Finder::findFiles('*');

Assert::throws(function() use ($finder) {
	$finder->getIterator();
}, 'Nette\InvalidStateException', 'Call in() or from() to specify directory to search.');
