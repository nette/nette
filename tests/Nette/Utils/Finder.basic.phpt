<?php

/**
 * Test: Nette\Utils\Finder basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Finder;



require __DIR__ . '/../bootstrap.php';



function export($iterator)
{
	$arr = array();
	foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
	return $arr;
}



// non-recursive file search
$finder = Finder::findFiles('file.txt')->in('files');
Assert::same(array('files/file.txt'), export($finder));


// recursive file search
$finder = Finder::findFiles('file.txt')->from('files');
Assert::same(array(
	'files/file.txt',
	'files/subdir/file.txt',
	'files/subdir/subdir2/file.txt',
), export($finder));


// recursive file search with depth limit
$finder = Finder::findFiles('file.txt')->from('files')->limitDepth(1);
Assert::same(array(
	'files/file.txt',
	'files/subdir/file.txt',
), export($finder));


// non-recursive file & directory search
$finder = Finder::find('file.txt')->in('files');
Assert::same(array(
	'files/file.txt',
	'files/images',
	'files/subdir',
), export($finder));


// recursive file & directory search
$finder = Finder::find('file.txt')->from('files');
Assert::same(array(
	'files/file.txt',
	'files/images',
	'files/subdir',
	'files/subdir/file.txt',
	'files/subdir/subdir2',
	'files/subdir/subdir2/file.txt',
), export($finder));


// recursive file & directory search in child-first order
$finder = Finder::find('file.txt')->from('files')->childFirst();
Assert::same(array(
	'files/file.txt',
	'files/images',
	'files/subdir/file.txt',
	'files/subdir/subdir2/file.txt',
	'files/subdir/subdir2',
	'files/subdir',
), export($finder));


// recursive file & directory search excluding folders
$finder = Finder::find('file.txt')->from('files')->exclude('images')->exclude('subdir2', '*.txt');
Assert::same(array(
	'files/file.txt',
	'files/subdir',
	'files/subdir/file.txt',
), export($finder));


// non-recursive directory search
$finder = Finder::findDirectories('subdir*')->in('files');
Assert::same(array(
	'files/subdir',
), export($finder));


// recursive directory search
$finder = Finder::findDirectories('subdir*')->from('files');
Assert::same(array(
	'files/subdir',
	'files/subdir/subdir2',
), export($finder));
