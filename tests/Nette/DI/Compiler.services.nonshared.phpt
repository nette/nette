<?php

/**
 * Test: Nette\DI\Compiler: nonshared services factories.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



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


Assert::false( $container->hasService('lorem') );
Assert::true( method_exists($container, 'createLorem') );

$params = new ReflectionParameter(array('Container', 'createLorem'), 0);
Assert::same( 'foo', $params->getName() );
Assert::same( 'Ipsum', $params->getClass()->getName() );
Assert::false( $params->isDefaultValueAvailable() );

$params = new ReflectionParameter(array('Container', 'createLorem'), 1);
Assert::same( 'bar', $params->getName() );
Assert::false( $params->getDefaultValue() );
