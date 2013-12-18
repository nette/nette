<?php

/**
 * Test: Nette\Configurator and headers.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Debugger Bar is not rendered in CLI mode');
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/configurator.headers.neon')
	->createContainer();


$headers = headers_list();
Assert::contains( 'X-Frame-Options: SAMEORIGIN', $headers );
Assert::contains( 'Content-Type: text/html; charset=utf-8', $headers );
Assert::contains( 'X-Powered-By: Nette Framework', $headers );
Assert::contains( 'A: b', $headers );
Assert::notContains( 'C:', $headers );



echo ' '; @ob_flush(); flush();

Assert::true( headers_sent() );

Assert::error(function(){
	$configurator = new Configurator;
	$configurator->setTempDirectory(TEMP_DIR);
	$container = $configurator->addConfig('files/configurator.headers.neon')
		->createContainer();
}, array(
	array(E_WARNING, 'Cannot modify header information - headers already sent %a%'),
	array(E_WARNING, 'Cannot modify header information - headers already sent %a%'),
	array(E_WARNING, 'Cannot modify header information - headers already sent %a%'),
	array(E_WARNING, 'Cannot modify header information - headers already sent %a%'),
));
