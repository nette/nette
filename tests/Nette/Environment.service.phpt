<?php

/**
 * Test: Nette\Environment services.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



dump( Environment::getHttpResponse()->class, 'Environment::getHttpResponse' );

dump( Environment::getApplication()->class, 'Environment::getApplication' );

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');
dump( Environment::getCache('my')->class, 'Environment::getCache(...)' );

/* in PHP 5.3
Environment::setServiceAlias('Nette\Web\IUser', 'xyz');
dump( Environment::getXyz()->class, 'Environment::getXyz(...)' );
*/



__halt_compiler();

------EXPECT------
Environment::getHttpResponse: string(%d%) "%ns%HttpResponse"

Environment::getApplication: string(%d%) "%ns%Application"

Environment::getCache(...): string(%d%) "%ns%Cache"
