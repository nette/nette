<?php

/**
 * Test: Nette\Utils\Finder filters.
 */

use Nette\Utils\Finder,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function export($iterator)
{
	$arr = array();
	foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
	sort($arr);
	return $arr;
}


test(function() { // size filter
	$finder = Finder::findFiles('*')->size('>8kB')->from('files');
	Assert::same(array(
		'files/images/logo.gif',
	), export($finder));
});


test(function() {
	$finder = Finder::findFiles('*')->size('> 10')->size('< 100b')->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
		'files/subdir/readme',
	), export($finder));
});


test(function() {
	$finder = Finder::find('*')->size('>', 10)->size('< 100b')->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/images',
		'files/subdir',
		'files/subdir/file.txt',
		'files/subdir/readme',
		'files/subdir/subdir2',
	), export($finder));
});


test(function() { // date filter
	$finder = Finder::findFiles('*')->date('> 2020-01-02')->from('files');
	Assert::same(array(), export($finder));
});


test(function() { // custom filters
	Finder::extensionMethod('length', function($finder, $length) {
		return $finder->filter(function($file) use ($length) {
			return strlen($file->getFilename()) == $length;
		});
	});
});


test(function() {
	$finder = Finder::findFiles('*')->length(6)->from('files');
	Assert::same(array(
		'files/subdir/readme',
	), export($finder));
});
