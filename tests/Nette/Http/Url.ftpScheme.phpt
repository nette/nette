<?php

/**
 * Test: Nette\Http\Url ftp://
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


require __DIR__ . '/../bootstrap.php';


$url = new Url('ftp://ftp.is.co.za/rfc/rfc3986.txt');

Assert::same( 'ftp',  $url->scheme );
Assert::same( '',  $url->user );
Assert::same( '',  $url->password );
Assert::same( 'ftp.is.co.za',  $url->host );
Assert::same( 21,  $url->port );
Assert::same( '/rfc/rfc3986.txt',  $url->path );
Assert::same( '',  $url->query );
Assert::same( '',  $url->fragment );
Assert::same( 'ftp.is.co.za',  $url->authority );
Assert::same( 'ftp://ftp.is.co.za',  $url->hostUrl );
Assert::same( 'ftp://ftp.is.co.za/rfc/rfc3986.txt',  $url->absoluteUrl );
