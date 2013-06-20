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



// multiple mask
$finder = Finder::findFiles('*.txt', '*.gif')->from('files');
Assert::same(array(
	'files/file.txt',
	'files/images/logo.gif',
	'files/subdir/file.txt',
	'files/subdir/subdir2/file.txt',
), export($finder));

$finder = Finder::findFiles(array('*.txt', '*.gif'))->from('files');
Assert::same(array(
	'files/file.txt',
	'files/images/logo.gif',
	'files/subdir/file.txt',
	'files/subdir/subdir2/file.txt',
), export($finder));


// * mask
$finder = Finder::findFiles('*.txt', '*')->in('files/subdir');
Assert::same(array(
	'files/subdir/file.txt',
	'files/subdir/readme',
), export($finder));


// *.* mask
$finder = Finder::findFiles('*.*')->in('files/subdir');
Assert::same(array(
	'files/subdir/file.txt',
), export($finder));


// subdir mask
$finder = Finder::findFiles('*/*2/*')->from('files');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
), export($finder));


// excluding mask
$finder = Finder::findFiles('*')->exclude('*i*')->in('files/subdir');
Assert::same(array(
	'files/subdir/readme',
), export($finder));


// subdir excluding mask
$finder = Finder::findFiles('*')->exclude('*i*/*')->from('files');
Assert::same(array(
	'files/file.txt',
), export($finder));


// complex mask
$finder = Finder::findFiles('*[efd][a-z][!a-r]*')->from('files');
Assert::same(array(
	'files/images/logo.gif',
), export($finder));


$finder = Finder::findFiles('*2*/fi??.*')->from('files');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
), export($finder));


// anchored
$finder = Finder::findFiles('/f*')->from('files');
Assert::same(array(
	'files/file.txt',
), export($finder));

$finder = Finder::findFiles('/*/f*')->from('files');
Assert::same(array(
	'files/subdir/file.txt',
), export($finder));


// multidirs mask
$finder = Finder::findFiles('/**/f*')->from('files');
Assert::same(array(
	'files/subdir/file.txt',
	'files/subdir/subdir2/file.txt',
), export($finder));
