<?php

/**
 * Test: Nette\Http\Url query manipulation.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Url;



require __DIR__ . '/../bootstrap.php';



$uri = new Url('http://hostname/path?arg=value');
Assert::same( 'arg=value',  $uri->query );

$uri->appendQuery(NULL);
Assert::same( 'arg=value',  $uri->query );

$uri->appendQuery(array(NULL));
Assert::same( 'arg=value',  $uri->query );

$uri->appendQuery('arg2=value2');
Assert::same( 'arg=value&arg2=value2',  $uri->query );

$uri->appendQuery(array('arg3' => 'value3'));
Assert::same( 'arg=value&arg2=value2&arg3=value3',  $uri->query );

$uri->setQuery(array('arg3' => 'value3'));
Assert::same( 'arg3=value3',  $uri->query );
