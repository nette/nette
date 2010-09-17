<?php

/**
 * Test: Nette\Web\Uri file://
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../bootstrap.php';



$uri = new Uri('file://localhost/D:/dokumentace/rfc3986.txt');
Assert::same( 'file://localhost/D:/dokumentace/rfc3986.txt',  (string) $uri );
Assert::same( 'file',  $uri->scheme );
Assert::same( '',  $uri->user );
Assert::same( '',  $uri->password );
Assert::same( 'localhost',  $uri->host );
Assert::null( $uri->port );
Assert::same( '/D:/dokumentace/rfc3986.txt',  $uri->path );
Assert::same( '',  $uri->query );
Assert::same( '',  $uri->fragment );


$uri = new Uri('file:///D:/dokumentace/rfc3986.txt');
Assert::same( 'file://D:/dokumentace/rfc3986.txt',  (string) $uri );
Assert::same( 'file',  $uri->scheme );
Assert::same( '',  $uri->user );
Assert::same( '',  $uri->password );
Assert::same( '',  $uri->host );
Assert::null( $uri->port );
Assert::same( 'D:/dokumentace/rfc3986.txt',  $uri->path );
Assert::same( '',  $uri->query );
Assert::same( '',  $uri->fragment );
