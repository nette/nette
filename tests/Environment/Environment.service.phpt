<?php

/**
 * Test: Nette\Environment services.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



Assert::same( 'Nette\Web\HttpResponse', Environment::getHttpResponse()->reflection->name );


Assert::same( 'Nette\Application\Application', Environment::getApplication()->reflection->name );


Environment::setVariable('tempDir', __DIR__ . '/tmp');
Assert::same( 'Nette\Caching\Cache', Environment::getCache('my')->reflection->name );


/* in PHP 5.3
Environment::setServiceAlias('Nette\Web\IUser', 'xyz');
Assert::same('xyz', Environment::getXyz()->reflection->name );
*/
