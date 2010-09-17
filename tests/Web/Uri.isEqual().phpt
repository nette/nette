<?php

/**
 * Test: Nette\Web\Uri::isEqual()
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../bootstrap.php';



$uri = new Uri('http://exampl%65.COM?text=foo%20bar+foo&value');
$uri->canonicalize();
Assert::true( $uri->isEqual('http://example.com/?text=foo+bar%20foo&value') );
Assert::true( $uri->isEqual('http://example.com/?value&text=foo+bar%20foo') );
