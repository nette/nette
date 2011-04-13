<?php

/**
 * Test: Nette\Http\Url file://
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Url;



require __DIR__ . '/../bootstrap.php';



$uri = new Url('file://localhost/D:/dokumentace/rfc3986.txt');
Assert::same( 'file://localhost/D:/dokumentace/rfc3986.txt',  (string) $uri );
Assert::same( 'file',  $uri->scheme );
Assert::same( '',  $uri->user );
Assert::same( '',  $uri->password );
Assert::same( 'localhost',  $uri->host );
Assert::null( $uri->port );
Assert::same( '/D:/dokumentace/rfc3986.txt',  $uri->path );
Assert::same( '',  $uri->query );
Assert::same( '',  $uri->fragment );


$uri = new Url('file:///D:/dokumentace/rfc3986.txt');
Assert::same( 'file://D:/dokumentace/rfc3986.txt',  (string) $uri );
Assert::same( 'file',  $uri->scheme );
Assert::same( '',  $uri->user );
Assert::same( '',  $uri->password );
Assert::same( '',  $uri->host );
Assert::null( $uri->port );
Assert::same( 'D:/dokumentace/rfc3986.txt',  $uri->path );
Assert::same( '',  $uri->query );
Assert::same( '',  $uri->fragment );
