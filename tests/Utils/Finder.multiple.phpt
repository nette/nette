<?php

/**
 * Test: Nette\Finder multiple sources.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Finder;



require __DIR__ . '/../bootstrap.php';



function export($iterator)
{
	$arr = array();
	foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
	return $arr;
}



// recursive
$finder = Finder::find('*')->from('files/subdir/subdir2')->from('files/images');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));


$finder = Finder::find('*')->from('files/subdir/subdir2', 'files/images');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));


$finder = Finder::find('*')->from(array('files/subdir/subdir2', 'files/images'));
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));



// non-recursive
$finder = Finder::find('*')->in('files/subdir/subdir2')->in('files/images');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));


$finder = Finder::find('*')->in('files/subdir/subdir2', 'files/images');
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));


$finder = Finder::find('*')->in(array('files/subdir/subdir2', 'files/images'));
Assert::same(array(
	'files/subdir/subdir2/file.txt',
	'files/images/logo.gif',
), export($finder));
