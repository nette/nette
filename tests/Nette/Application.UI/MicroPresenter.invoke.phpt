<?php

/**
 * Test: NetteModule\MicroPresenter
 *
 * @author     Filip Procházka
 * @package    Nette\Application\UI
 * @subpackage UnitTests
 */

use Nette\Application\Request;



require __DIR__ . '/../bootstrap.php';



class Invokable extends Nette\Object
{
	public function __invoke($page, $id)
	{
		TestHelpers::note('Callback id ' . $id . ' page ' . $page);
	}
}


$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$presenter = new NetteModule\MicroPresenter($container);


$presenter->run(new Request('Nette:Micro', 'GET', array(
	'callback' => function ($id, $page) {
		TestHelpers::note('Callback id ' . $id . ' page ' . $page);
	},
	'id' => 1,
	'page' => 2,
)));
Assert::equal(array(
	'Callback id 1 page 2'
), TestHelpers::fetchNotes());


$presenter->run(new Request('Nette:Micro', 'GET', array(
	'callback' => new Invokable(),
	'id' => 1,
	'page' => 2,
)));
Assert::equal(array(
	'Callback id 1 page 2'
), TestHelpers::fetchNotes());
