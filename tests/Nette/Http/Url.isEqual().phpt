<?php

/**
 * Test: Nette\Http\Url::isEqual()
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Url;



require __DIR__ . '/../bootstrap.php';



$uri = new Url('http://exampl%65.COM?text=foo%20bar+foo&value');
$uri->canonicalize();
Assert::true( $uri->isEqual('http://example.com/?text=foo+bar%20foo&value') );
Assert::true( $uri->isEqual('http://example.com/?value&text=foo+bar%20foo') );
