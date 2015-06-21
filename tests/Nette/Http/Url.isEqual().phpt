<?php

/**
 * Test: Nette\Http\Url::isEqual()
 */

use Nette\Http\Url;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://exampl%65.COM?text=foo%20bar+foo&value');
Assert::true($url->isEqual('http://example.com/?text=foo+bar%20foo&value'));
Assert::true($url->isEqual('http://example.com/?value&text=foo+bar%20foo'));
Assert::false($url->isEqual('http://example.com/?value&text=foo+bar%20foo#abc'));
Assert::false($url->isEqual('http://example.com/?text=foo+bar%20foo'));
Assert::false($url->isEqual('https://example.com/?text=foo+bar%20foo&value'));
Assert::false($url->isEqual('http://example.org/?text=foo+bar%20foo&value'));
Assert::false($url->isEqual('http://example.com/path?text=foo+bar%20foo&value'));


$url = new Url('http://example.com/?arr[]=item1&arr[]=item2');
Assert::true($url->isEqual('http://example.com/?arr[0]=item1&arr[1]=item2'));
Assert::false($url->isEqual('http://example.com/?arr[1]=item1&arr[0]=item2'));


$url = new Url('http://example.com/?a=9999&b=127.0.0.1&c=1234&d=123456789');
Assert::true($url->isEqual('http://example.com/?d=123456789&a=9999&b=127.0.0.1&c=1234'));


$url = new Url('http://example.com/?a=123&b=456');
Assert::false($url->isEqual('http://example.com/?a=456&b=123'));


$url = new Url('http://user:pass@example.com');
Assert::true($url->isEqual('http://example.com'));


$url = new Url('ftp://user:pass@example.com');
Assert::false($url->isEqual('ftp://example.com'));
