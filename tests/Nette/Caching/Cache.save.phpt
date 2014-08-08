<?php

/**
 * Test: Nette\Caching\Cache save().
 */

use Nette\Caching\Cache,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Cache.inc';


// save value with dependencies
$storage = new testStorage();
$cache = new Cache($storage, 'ns');
$dependencies = array(Cache::TAGS => 'tag');

$cache->save('key', 'value', $dependencies);

Assert::equal('value', $cache['key']['data']);
Assert::equal($dependencies, $cache['key']['dependencies']);


// save callback return value
$storage = new testStorage();
$cache = new Cache($storage, 'ns');

$cache->save('key', function() {
	return 'value';
});

Assert::equal('value', $cache['key']['data']);
Assert::equal(array(), $cache['key']['dependencies']);


// save callback return value with dependencies
$storage = new testStorage();
$cache = new Cache($storage, 'ns');
$dependencies = array(Cache::TAGS => 'tag');

$cache->save('key', function() {
	return 'value';
}, $dependencies);

Assert::equal('value', $cache['key']['data']);
Assert::equal($dependencies, $cache['key']['dependencies']);
