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



Assert::same( 'Nette\Http\Response', get_class(Environment::getHttpResponse()) );


Environment::setVariable('appDir', TEMP_DIR);
Assert::same( 'Nette\Application\Application', get_class(Environment::getApplication()) );


Environment::setVariable('tempDir', TEMP_DIR);
Assert::same( 'Nette\Caching\Cache', get_class(Environment::getCache('my')) );
