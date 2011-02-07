<?php

/**
 * Test: Nette\Finder filters.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 * @phpversion 5.3
 */

use Nette\Finder,
	Nette\Tools;



require __DIR__ . '/../bootstrap.php';



function export($iterator)
{
	$arr = array();
	foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
	return $arr;
}



// size filter
$finder = Finder::findFiles('*')->size('>8kB')->from('files');
Assert::same(array(
	'files/images/logo.gif',
), export($finder));


$finder = Finder::findFiles('*')->size('> 10')->size('< 100b')->from('files');
Assert::same(array(
	'files/file.txt',
	'files/subdir/file.txt',
	'files/subdir/readme',
), export($finder));



$finder = Finder::find('*')->size('>', 10)->size('< 100b')->from('files');
Assert::same(array(
	'files/file.txt',
	'files/images',
	'files/subdir',
	'files/subdir/file.txt',
	'files/subdir/readme',
	'files/subdir/subdir2',
), export($finder));



$finder = Finder::findDirectories('*')->size('>', 10)->size('< 100b')->from('files');
Assert::same(array(), export($finder));



// date filter
$finder = Finder::findFiles('*')->date('> 2020-01-02')->from('files');
Assert::same(array(), export($finder));



// custom filters
Finder::extensionMethod('length', function($finder, $length) {
	return $finder->filter(function($file) use ($length) {
		return strlen($file->getFilename()) == $length;
	});
});

$finder = Finder::findFiles('*')->length(6)->from('files');
Assert::same(array(
	'files/subdir/readme',
), export($finder));
