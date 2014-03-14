<?php

/**
 * Test: Nette\Utils\Finder basic usage.
 *
 * @author     David Grudl
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


test(function() { // non-recursive file search
	$finder = Finder::findFiles('file.txt')->in('files');
	Assert::same(array('files/file.txt'), export($finder));
});


test(function() { // recursive file search
	$finder = Finder::findFiles('file.txt')->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // recursive file search with depth limit
	$finder = Finder::findFiles('file.txt')->from('files')->limitDepth(1);
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
	), export($finder));
});


test(function() { // non-recursive file & directory search
	$finder = Finder::find('file.txt')->in('files');
	Assert::same(array(
		'files/file.txt',
	), export($finder));
});


test(function() { // recursive file & directory search
	$finder = Finder::find('file.txt')->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // recursive file & directory search in child-first order
	$finder = Finder::find('file.txt')->from('files')->childFirst();
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // recursive file & directory search excluding folders
	$finder = Finder::find('file.txt')->from('files')->exclude('images')->exclude('subdir2', '*.txt');
	Assert::same(array(
		'files/file.txt',
		'files/subdir/file.txt',
	), export($finder));
});


test(function() { // non-recursive directory search
	$finder = Finder::findDirectories('subdir*')->in('files');
	Assert::same(array(
		'files/subdir',
	), export($finder));
});


test(function() { // recursive directory search
	$finder = Finder::findDirectories('subdir*')->from('files');
	Assert::same(array(
		'files/subdir',
		'files/subdir/subdir2',
	), export($finder));
});
