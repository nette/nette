<?php

/**
 * Test: Nette\Http\Url::isEqual()
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://exampl%65.COM?text=foo%20bar+foo&value');
$url->canonicalize();
Assert::true( $url->isEqual('http://example.com/?text=foo+bar%20foo&value') );
Assert::true( $url->isEqual('http://example.com/?value&text=foo+bar%20foo') );
