<?php

/**
 * Test: Nette\Utils\SafeStream stress test.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 * @skip       Requires more robust NetteTestCase implementation.
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



// TODO: run it twice (or more) simultaneously


function randomStr()
{
	$s = str_repeat('LaTrine', rand(100, 20000));
	return sha1($s, TRUE) . $s;
}



function checkStr($s)
{
	return substr($s, 0, 20) === sha1(substr($s, 20), TRUE);
}



define('COUNT_FILES', 3);
set_time_limit(0);


// clear playground
for ($i=0; $i<=COUNT_FILES; $i++) {
	file_put_contents('safe://tmp/testfile'.$i, randomStr());
}


// test loop
Debugger::timer();

$hits = array('ok' => 0, 'notfound' => 0, 'error' => 0, 'cantwrite' => 0, 'cantdelete' => 0);
for ($counter=0; $counter<1000; $counter++) {
	// write
	$ok = @file_put_contents('safe://tmp/testfile'.rand(0, COUNT_FILES), randomStr());
	if ($ok === FALSE) $hits['cantwrite']++;

	// delete
//    $ok = @unlink('safe://testfile'.rand(0, COUNT_FILES));
//    if (!$ok) $hits['cantdelete']++;

	// read
	$res = @file_get_contents('safe://tmp/testfile'.rand(0, COUNT_FILES));

	// compare
	if ($res === FALSE)  $hits['notfound']++;
	elseif (checkStr($res)) $hits['ok']++;
	else $hits['error']++;
}
$time = Debugger::timer();


Assert::same(0, $hits['error']);

// expected results are:
//    [ok] => 1000       // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
//    [notfound] => 0    // means 'file not found', should be 0 if unlink() is not used
//    [error] => 0,      // means 'file contents is damaged', MUST be 0
//    [cantwrite] => ?,  // means 'somebody else is writing this file'
//    [cantdelete] => 0  // means 'unlink() has timeout',  should be 0

//echo "\ntakes $time ms";
