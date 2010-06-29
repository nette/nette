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



T::dump( Environment::getHttpResponse()->reflection->name, 'Environment::getHttpResponse' );

T::dump( Environment::getApplication()->reflection->name, 'Environment::getApplication' );

Environment::setVariable('tempDir', __DIR__ . '/tmp');
T::dump( Environment::getCache('my')->reflection->name, 'Environment::getCache(...)' );

/* in PHP 5.3
Environment::setServiceAlias('Nette\Web\IUser', 'xyz');
T::dump( Environment::getXyz()->reflection->name, 'Environment::getXyz(...)' );
*/



__halt_compiler() ?>

------EXPECT------
Environment::getHttpResponse: string(%d%) "%ns%HttpResponse"

Environment::getApplication: string(%d%) "%ns%Application"

Environment::getCache(...): string(%d%) "%ns%Cache"
