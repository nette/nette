<?php

/**
 * Test: Nette\Http\Url query manipulation.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


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
