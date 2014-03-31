<?php

/**
 * Test: Nette\Utils\SafeStream basic usage.
 *
 * @author     David Grudl
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Nette\Utils\SafeStream::register();

// actually it creates temporary file
$handle = fopen('safe://myfile.txt', 'x');
fwrite($handle, 'atomic and safe');
// and now rename it
fclose($handle);

Assert::true(is_file('safe://myfile.txt'));
Assert::same('atomic and safe', file_get_contents('safe://myfile.txt'));

// removes file thread-safe way
unlink('safe://myfile.txt');

// this is not thread safe - don't relay on returned value
Assert::false(is_file('safe://myfile.txt'));
