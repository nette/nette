<?php

/**
 * Test: Nette\Web\Uri ftp://
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../initialize.php';



$uri = new Uri('ftp://ftp.is.co.za/rfc/rfc3986.txt');

Assert::same( 'ftp',  $uri->scheme );
Assert::same( '',  $uri->user );
Assert::same( '',  $uri->password );
Assert::same( 'ftp.is.co.za',  $uri->host );
Assert::same( 21,  $uri->port );
Assert::same( '/rfc/rfc3986.txt',  $uri->path );
Assert::same( '',  $uri->query );
Assert::same( '',  $uri->fragment );
Assert::same( 'ftp.is.co.za',  $uri->authority );
Assert::same( 'ftp://ftp.is.co.za',  $uri->hostUri );
Assert::same( 'ftp://ftp.is.co.za/rfc/rfc3986.txt',  $uri->absoluteUri );
