<?php

/**
 * Test: Nette\Caching\Storages\FileStorage sliding expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @multiple   5
 */

use Nette\Caching\Storages\FileStorage,
	Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


set_time_limit(0);


function randomStr()
{
	$s = str_repeat('LaTrine', rand(10, 2000));
	return sha1($s, TRUE) . $s;
}


function checkStr($s)
{
	return substr($s, 0, 20) === sha1(substr($s, 20), TRUE);
}


define('COUNT_FILES', 3);


$storage = new FileStorage(TEMP_DIR);


// clear playground
for ($i=0; $i<=COUNT_FILES; $i++) {
	$storage->write($i, randomStr(), array());
}


// test loop
Debugger::timer();

$hits = array('ok' => 0, 'notfound' => 0, 'error' => 0, 'cantwrite' => 0, 'cantdelete' => 0);
for ($counter=0; $counter<1000; $counter++) {
	// write
	$ok = $storage->write(rand(0, COUNT_FILES), randomStr(), array());
	if ($ok === FALSE) $hits['cantwrite']++;

	// remove
	//$ok = $storage->remove(rand(0, COUNT_FILES));
	//if (!$ok) $hits['cantdelete']++;

	// read
	$res = $storage->read(rand(0, COUNT_FILES));

	// compare
	if ($res === NULL) $hits['notfound']++;
	elseif (checkStr($res)) $hits['ok']++;
	else $hits['error']++;
}
$time = Debugger::timer();


Assert::same( array(
	'ok' => 1000,
	'notfound' => 0,
	'error' => 0,
	'cantwrite' => 0,
	'cantdelete' => 0,
), $hits );

// expected results are:
//    [ok] => 1000       // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
//    [notfound] => 0    // means 'file not found', should be 0 if delete() is not used
//    [error] => 0,      // means 'file contents is damaged', MUST be 0
//    [cantwrite] => ?,  // means 'somebody else is writing this file'
//    [cantdelete] => 0  // means 'delete() has timeout',  should be 0

Assert::same(0, $hits['error']);
// takes $time ms
