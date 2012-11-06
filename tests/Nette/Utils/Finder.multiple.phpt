<?php

/**
 * Test: Nette\Utils\Finder multiple sources.
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



// recursive
$finder = Finder::find('*')->from('files/subdir/subdir2', 'files/images');
Assert::same(array(
	'files/images/logo.gif',
	'files/subdir/subdir2/file.txt',
), export($finder));


$finder = Finder::find('*')->from(array('files/subdir/subdir2', 'files/images'));
Assert::same(array(
	'files/images/logo.gif',
	'files/subdir/subdir2/file.txt',
), export($finder));

Assert::exception(function() {
	Finder::find('*')->from('files/subdir/subdir2')->from('files/images');
}, 'Nette\InvalidStateException', '');




// non-recursive
$finder = Finder::find('*')->in('files/subdir/subdir2', 'files/images');
Assert::same(array(
	'files/images/logo.gif',
	'files/subdir/subdir2/file.txt',
), export($finder));


$finder = Finder::find('*')->in(array('files/subdir/subdir2', 'files/images'));
Assert::same(array(
	'files/images/logo.gif',
	'files/subdir/subdir2/file.txt',
), export($finder));

Assert::exception(function() {
	Finder::find('*')->in('files/subdir/subdir2')->in('files/images');
}, 'Nette\InvalidStateException', '');
