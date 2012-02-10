<?php

/**
 * Test: Nette\DI\ContainerBuilder and nested accessor.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



$builder = new DI\ContainerBuilder;
$builder->addDefinition('nested')
	->setClass('Nette\DI\NestedAccessor', array('@container', 'nested'));

$builder->addDefinition('nested.one')
	->setClass('stdClass');


$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';


$container = new Container;

Assert::true( $container->nested->one instanceof stdClass );

Assert::false( isset($container->nested_one) );

