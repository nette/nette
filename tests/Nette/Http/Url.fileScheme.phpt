<?php

/**
 * Test: Nette\Http\Url file://
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$url = new Url('file://localhost/D:/dokumentace/rfc3986.txt');
	Assert::same( 'file://localhost/D:/dokumentace/rfc3986.txt',  (string) $url );
	Assert::same( 'file',  $url->scheme );
	Assert::same( '',  $url->user );
	Assert::same( '',  $url->password );
	Assert::same( 'localhost',  $url->host );
	Assert::null( $url->port );
	Assert::same( '/D:/dokumentace/rfc3986.txt',  $url->path );
	Assert::same( '',  $url->query );
	Assert::same( '',  $url->fragment );
});


test(function() {
	$url = new Url('file:///D:/dokumentace/rfc3986.txt');
	Assert::same( 'file://D:/dokumentace/rfc3986.txt',  (string) $url );
	Assert::same( 'file',  $url->scheme );
	Assert::same( '',  $url->user );
	Assert::same( '',  $url->password );
	Assert::same( '',  $url->host );
	Assert::null( $url->port );
	Assert::same( 'D:/dokumentace/rfc3986.txt',  $url->path );
	Assert::same( '',  $url->query );
	Assert::same( '',  $url->fragment );
});
