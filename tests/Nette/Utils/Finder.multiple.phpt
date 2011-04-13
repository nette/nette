<?php

/**
 * Test: Nette\Utils\Finder multiple sources.
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



// recursive
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

try {
	Finder::find('*')->from('files/subdir/subdir2')->from('files/images');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', '', $e );
}




// non-recursive
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

try {
	Finder::find('*')->in('files/subdir/subdir2')->in('files/images');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', '', $e );
}
