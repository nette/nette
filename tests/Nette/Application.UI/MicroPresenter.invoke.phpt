<?php

/**
 * Test: NetteModule\MicroPresenter
 *
 * @author     Filip Procházka
 */

use Nette\Application\Request,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Invokable extends Nette\Object
{
	public function __invoke($page, $id)
	{
		Notes::add('Callback id ' . $id . ' page ' . $page);
	}
}


test(function() {
	$presenter = new NetteModule\MicroPresenter;

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


test(function() {
	$presenter = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', array(
		'callback' => new Invokable(),
		'id' => 1,
		'page' => 2,
	)));
	Assert::same(array(
		'Callback id 1 page 2'
	), Notes::fetch());
});
