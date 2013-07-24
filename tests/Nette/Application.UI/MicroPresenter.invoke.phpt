<?php

/**
 * Test: NetteModule\MicroPresenter
 *
 * @author     Filip Procházka
 * @package    Nette\Application\UI
 */

use Nette\Application\Request;


require __DIR__ . '/../bootstrap.php';


class Invokable extends Nette\Object
{
	public function __invoke($page, $id)
	{
		Notes::add('Callback id ' . $id . ' page ' . $page);
	}
}


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

test(function() use ($container) {
	$presenter = new NetteModule\MicroPresenter($container);

	$presenter->run(new Request('Nette:Micro', 'GET', array(
		'callback' => function($id, $page) {
			Notes::add('Callback id ' . $id . ' page ' . $page);
		},
		'id' => 1,
		'page' => 2,
	)));
	Assert::same(array(
		'Callback id 1 page 2'
	), Notes::fetch());
});


test(function() use ($container) {
	$presenter = new NetteModule\MicroPresenter($container);

	$presenter->run(new Request('Nette:Micro', 'GET', array(
		'callback' => new Invokable(),
		'id' => 1,
		'page' => 2,
	)));
	Assert::same(array(
		'Callback id 1 page 2'
	), Notes::fetch());
});
