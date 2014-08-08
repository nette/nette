<?php

/**
 * Test: Nette\Caching\Cache load().
 */

use Nette\Caching\Cache,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Cache.inc';


// load twice with fallback
$storage = new TestStorage();
$cache = new Cache($storage, 'ns');

$value = $cache->load('key', function() {
	return 'value';
});
Assert::equal('value', $value);

$data = $cache->load('key', function() {
	return "won't load this value"; // will read from storage
});
Assert::equal('value', $data['data']);


// load twice with fallback, pass dependencies
$dependencies = array(Cache::TAGS => 'tag');
$storage = new TestStorage();
$cache = new Cache($storage, 'ns');

$value = $cache->load('key', function(& $deps) use ($dependencies) {
	$deps = $dependencies;
	return 'value';
});
Assert::equal('value', $value);

$data = $cache->load('key', function() {
	return "won't load this value"; // will read from storage
});
Assert::equal('value', $data['data']);
Assert::equal($dependencies, $data['dependencies']);
