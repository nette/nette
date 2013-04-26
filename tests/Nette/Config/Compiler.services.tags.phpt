<?php

/**
 * Test: Nette\Config\Compiler: services tags.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



$loader = new Config\Loader;
$compiler = new Config\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.tags.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same(array(
	'types' => array(
		'stdclass' => array('lorem'),
		'nette\\object' => array('container'),
		'nette\\di\\container' => array('container'),
	),
	'tags' => array(
		'a' => array('lorem' => TRUE),
		'b' => array('lorem' => 'c'),
		'd' => array('lorem' => array('e')),
	),
), $container->meta );

Assert::same( array('lorem' => TRUE), $container->findByTag('a') );
Assert::same( array(), $container->findByTag('x') );
