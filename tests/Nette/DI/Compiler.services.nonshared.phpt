<?php

/**
 * Test: Nette\DI\Compiler: nonshared services factories.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Ipsum
{
}

class Lorem
{
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.nonshared.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->hasService('lorem') );
Assert::true( method_exists($container, 'createServiceLorem') );

$params = new ReflectionParameter(array('Container', 'createServiceLorem'), 0);
Assert::same( 'foo', $params->getName() );
Assert::same( 'Ipsum', $params->getClass()->getName() );
Assert::false( $params->isDefaultValueAvailable() );

$params = new ReflectionParameter(array('Container', 'createServiceLorem'), 1);
Assert::same( 'bar', $params->getName() );
Assert::false( $params->getDefaultValue() );
