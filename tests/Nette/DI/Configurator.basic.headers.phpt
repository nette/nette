<?php

/**
 * Test: Nette\Configurator and headers.
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Debugger Bar is not rendered in CLI mode');
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->createContainer();


$headers = headers_list();
Assert::contains( 'X-Frame-Options: SAMEORIGIN', $headers );
Assert::contains( 'Content-Type: text/html; charset=utf-8', $headers );
Assert::contains( 'X-Powered-By: Nette Framework', $headers );



echo ' '; @ob_flush(); flush();

Assert::true( headers_sent() );

Assert::error(function(){
	$configurator = new Configurator;
	$configurator->setTempDirectory(TEMP_DIR);
	$container = $configurator->createContainer();
}, array(
	array(E_WARNING, 'Cannot modify header information - headers already sent %a%'),
));
