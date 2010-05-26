<?php

/**
 * Test: Nette\Web\Uri query manipulation.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../NetteTest/initialize.php';



$uri = new Uri('http://hostname/path?arg=value');
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
