<h1>Nette::IO::SafeStream stress test - run it twice (or more) simultaneously</h1>

<pre>
<?php


require_once '../../Nette/Debug.php';
require_once '../../Nette/IO/SafeStream.php';

/*use Nette::Debug;*/


set_time_limit(0);



function timer()
{
    static $lastTime = 0;
    list($usec, $sec) = explode(" ",microtime(false));
    $delta = ((float) $usec + (float) $sec) - $lastTime;
    $lastTime += $delta;
    return $delta;
}



function randomStr()
{
    $s = str_repeat('LaTrine', rand(100, 20000));
    $hash = sha1($s);
    return serialize(array($s, $hash));
}



function checkStr($s)
{
    @list($s, $hash) = unserialize($s);
    return $hash === sha1($s);
}




define('COUNT_FILES', 3);


// clear playground
for ($i=0; $i<=COUNT_FILES; $i++)
    file_put_contents('safe://tmp/testfile'.$i, randomStr());


// test loop
echo "Testing (with SafeStream)...\n";
timer();

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
$time = timer();


echo "Results:\n";
Debug::dump($hits);

// expected results are:
//    [ok] => 1000       // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
//    [notfound] => 0    // means "file not found", should be 0 is unlink() is not used
//    [error] => 0,      // means "file contents is damaged", MUST be 0
//    [cantwrite] => ?,  // means "somebody else is writing this file"
//    [cantdelete] => 0  // means "unlink() has timeout",  should be 0

echo $hits['error'] == 0 ? 'PASSED' : 'NOT PASSED!';
echo "\ntakes $time ms";
