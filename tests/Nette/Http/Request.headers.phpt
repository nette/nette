<?php

/**
 * Test: Nette\Http\Request headers.
 */

use Nette\Http,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$request = new Http\Request(new Http\UrlScript);
	Assert::same(array(), $request->getHeaders());
});

test(function() {
	$request = new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array());
	Assert::same(array(), $request->getHeaders());
});

test(function() {
	$request = new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array(
		'one' => '1',
		'TWO' => '2',
		'X-Header' => 'X',
	));

	Assert::same(array(
		'one' => '1',
		'two' => '2',
		'x-header' => 'X',
	), $request->getHeaders());
	Assert::same('1', $request->getHeader('One'));
	Assert::same('2', $request->getHeader('Two'));
	Assert::same('X', $request->getHeader('X-Header'));
});
