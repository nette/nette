<h1>Nette::Caching::FileStorage stress test - run it twice (or more) simultaneously</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/


set_time_limit(0);



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


$storage = new /*Nette::Caching::*/FileStorage(dirname(__FILE__) . '/tmp/testfile');


// clear playground
for ($i=0; $i<=COUNT_FILES; $i++) {
	$storage->write($i, randomStr(), array());
}


// test loop
echo "Testing...\n";
Debug::timer();

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
$time = Debug::timer();


echo "Results:\n";
Debug::dump($hits);

// expected results are:
//    [ok] => 1000       // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
//    [notfound] => 0    // means "file not found", should be 0 if delete() is not used
//    [error] => 0,      // means "file contents is damaged", MUST be 0
//    [cantwrite] => ?,  // means "somebody else is writing this file"
//    [cantdelete] => 0  // means "delete() has timeout",  should be 0

echo $hits['error'] == 0 ? 'PASSED' : 'NOT PASSED!';
echo "\ntakes $time ms";
