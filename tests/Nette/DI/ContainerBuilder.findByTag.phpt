<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: findByTag()
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');
$builder->addDefinition('two')
	->setClass('stdClass')
	->addTag('debugPanel', TRUE);
$builder->addDefinition('three')
	->setClass('stdClass')
	->addTag('component');
$builder->addDefinition('five')
	->setClass('stdClass')
	->addTag('debugPanel', array(1, 2, 3))
	->addTag('typeHint', 'Service');


test(function() use ($builder) { // compile-time
	Assert::same( array(
		'five' => 'Service',
	), $builder->findByTag('typeHint') );

	Assert::same( array(
		'two' => TRUE,
		'five' => array(1, 2, 3),
	), $builder->findByTag('debugPanel') );

	Assert::same( array(
		'three' => TRUE,
	), $builder->findByTag('component') );

	Assert::same( array(), $builder->findByTag('unknown') );
});


test(function() use ($builder) { // run-time
	$code = implode('', $builder->generateClasses());
	file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
	require TEMP_DIR . '/code.php';

	$container = new Container;

	Assert::same( array(
		'five' => 'Service',
	), $container->findByTag('typeHint') );

	Assert::same( array(
		'five' => array(1, 2, 3),
		'two' => TRUE,
	), $container->findByTag('debugPanel') );

	Assert::same( array(), $container->findByTag('unknown') );
});
