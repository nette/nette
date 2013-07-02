<?php

/**
 * Test: Nette\DI\ContainerBuilder and nested accessor.
 *
 * @author     David Grudl
 * @package    Nette\DI
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

Assert::type( 'stdClass', $container->nested->one );

Assert::false( isset($container->nested_one) );
