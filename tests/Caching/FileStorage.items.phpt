<?php

/**
 * Test: FileStorage items dependency test.
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

// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';

NetteTestHelpers::purge($tempDir);


$cache = new Cache(new /*Nette\Caching\*/FileStorage($tempDir));


message('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => array('dependent'),
));

dump( isset($cache[$key]), 'Is cached?' );

message('Modifing dependent cached item');
$cache['dependent'] = 'hello world';

dump( isset($cache[$key]), 'Is cached?' );

message('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

dump( isset($cache[$key]), 'Is cached?' );

message('Modifing dependent cached item');
sleep(2);
$cache['dependent'] = 'hello europe';

dump( isset($cache[$key]), 'Is cached?' );

message('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

dump( isset($cache[$key]), 'Is cached?' );

message('Deleting dependent cached item');
$cache['dependent'] = NULL;

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Writing cache...

Is cached? bool(TRUE)

Modifing dependent cached item

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Modifing dependent cached item

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Deleting dependent cached item

Is cached? bool(FALSE)
