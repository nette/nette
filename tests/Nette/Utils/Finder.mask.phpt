<?php

/**
 * Test: Nette\Utils\Finder mask tests.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Finder;


require __DIR__ . '/../bootstrap.php';


function export($iterator)
{
	$arr = array();
	foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
	sort($arr);
	return $arr;
}


test(function() { // multiple mask
	$finder = Finder::findFiles('*.txt', '*.gif')->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/images/logo.gif',
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() {
	$finder = Finder::findFiles(array('*.txt', '*.gif'))->from('files');
	Assert::same(array(
		'files/file.txt',
		'files/images/logo.gif',
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // * mask
	$finder = Finder::findFiles('*.txt', '*')->in('files/subdir');
	Assert::same(array(
		'files/subdir/file.txt',
		'files/subdir/readme',
	), export($finder));
});


test(function() { // *.* mask
	$finder = Finder::findFiles('*.*')->in('files/subdir');
	Assert::same(array(
		'files/subdir/file.txt',
	), export($finder));
});


test(function() { // subdir mask
	$finder = Finder::findFiles('*/*2/*')->from('files');
	Assert::same(array(
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // excluding mask
	$finder = Finder::findFiles('*')->exclude('*i*')->in('files/subdir');
	Assert::same(array(
		'files/subdir/readme',
	), export($finder));
});


test(function() { // subdir excluding mask
	$finder = Finder::findFiles('*')->exclude('*i*/*')->from('files');
	Assert::same(array(
		'files/file.txt',
	), export($finder));
});


test(function() { // complex mask
	$finder = Finder::findFiles('*[efd][a-z][!a-r]*')->from('files');
	Assert::same(array(
		'files/images/logo.gif',
	), export($finder));
});


test(function() {
	$finder = Finder::findFiles('*2*/fi??.*')->from('files');
	Assert::same(array(
		'files/subdir/subdir2/file.txt',
	), export($finder));
});


test(function() { // anchored
	$finder = Finder::findFiles('/f*')->from('files');
	Assert::same(array(
		'files/file.txt',
	), export($finder));
});


test(function() {
	$finder = Finder::findFiles('/*/f*')->from('files');
	Assert::same(array(
		'files/subdir/file.txt',
	), export($finder));
});


test(function() { // multidirs mask
	$finder = Finder::findFiles('/**/f*')->from('files');
	Assert::same(array(
		'files/subdir/file.txt',
		'files/subdir/subdir2/file.txt',
	), export($finder));
});
