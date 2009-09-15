<?php

/**
 * Test: Memcached files dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new /*Nette\Caching\*/MemcachedStorage('localhost'));


$dependentFile = dirname(__FILE__) . '/tmp/spec.file';
@unlink($dependentFile);

message('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => array(
		__FILE__,
		$dependentFile,
	),
));

dump( isset($cache[$key]), 'Is cached?' );

message('Modifing dependent file');
file_put_contents($dependentFile, 'a');

dump( isset($cache[$key]), 'Is cached?' );

message('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));

dump( isset($cache[$key]), 'Is cached?' );

message('Modifing dependent file');
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Writing cache...

Is cached? bool(TRUE)

Modifing dependent file

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Modifing dependent file

Is cached? bool(FALSE)
