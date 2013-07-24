<?php

/**
 * Test: Nette\Utils\SafeStream basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */


require __DIR__ . '/../bootstrap.php';


// actually it creates temporary file
$handle = fopen('safe://myfile.txt', 'x');
fwrite($handle, 'atomic and safe');
// and now rename it
fclose($handle);

// removes file thread-safe way
unlink('safe://myfile.txt');

// this is not thread safe - don't relay on returned value
$ok = is_file('safe://SafeStream.php');
