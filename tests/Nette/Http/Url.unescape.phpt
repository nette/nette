<?php

/**
 * Test: Nette\Http\Url unescape.
 */

use Nette\Http\Url,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( 'foo + bar', Url::unescape('foo + bar') );
Assert::same( 'foo + bar', Url::unescape('foo + bar', '') );
Assert::same( 'foo', Url::unescape('%66%6F%6F', '') );
Assert::same( 'f%6F%6F', Url::unescape('%66%6F%6F', 'o') );
Assert::same( '%66oo', Url::unescape('%66%6F%6F', 'f') );
Assert::same( '%66%6F%6F', Url::unescape('%66%6F%6F', 'fo') );
Assert::same( '%66%6f%6f', Url::unescape('%66%6f%6f', 'fo') );
Assert::same( "%00\x01%02", Url::unescape('%00%01%02', "\x00\x02") );
