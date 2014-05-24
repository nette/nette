<?php

/**
 * Test: Nette\Http\Url query manipulation.
 */

use Nette\Http\Url,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://hostname/path?arg=value');
Assert::same( 'arg=value',  $url->query );

$url->appendQuery(NULL);
Assert::same( 'arg=value',  $url->query );

$url->appendQuery(array(NULL));
Assert::same( 'arg=value',  $url->query );

$url->appendQuery('arg2=value2');
Assert::same( 'arg=value&arg2=value2',  $url->query );

$url->appendQuery(array('arg3' => 'value3'));
Assert::same( 'arg=value&arg2=value2&arg3=value3',  $url->query );

$url->setQuery(array('arg3' => 'value3'));
Assert::same( 'arg3=value3',  $url->query );

$url->setQuery(array('arg' => 'value'));
Assert::same( 'value', $url->getQueryParameter('arg') );
Assert::same( NULL, $url->getQueryParameter('invalid') );
Assert::same( 123, $url->getQueryParameter('invalid', 123) );

$url->setQueryParameter('arg2', 'abc');
Assert::same( 'abc', $url->getQueryParameter('arg2') );
$url->setQueryParameter('arg2', 'def');
Assert::same( 'def', $url->getQueryParameter('arg2') );
$url->setQueryParameter('arg2', NULL);
Assert::same( NULL, $url->getQueryParameter('arg2') );
