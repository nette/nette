<?php

/**
 * Test: Nette\Environment services.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Assert::same( 'Nette\Http\Response', Environment::getHttpResponse()->reflection->name );


Assert::same( 'Nette\Application\Application', Environment::getApplication()->reflection->name );


Environment::setVariable('tempDir', __DIR__ . '/tmp');
Assert::same( 'Nette\Caching\Cache', Environment::getCache('my')->reflection->name );


/* in PHP 5.3
Nette\Environment::setServiceAlias('Nette\Http\IUser', 'xyz');
Assert::same('xyz', Nette\Environment::getXyz()->reflection->name );
*/
