<?php

/**
 * Test: Nette\Application\UI\PresenterComponent::getParameters()
 *
 * @author     Jan Skrasek
 * @package    Nette\Application\UI
 */

use Nette\Application;
use Tester\Assert;



require __DIR__ . '/../bootstrap.php';



class TestPresenter extends Application\UI\Presenter
{
	/** @persistent */
	public $lang = 'default';

	public function actionDefault()
	{
		$this->terminate();
	}
}

$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$presenter = new TestPresenter;
$container->callMethod($presenter->injectPrimary);
$presenter->run(new Nette\Application\Request('Test', '', array('lang' => 'default')));

$parameters = $presenter->getParameters();
Assert::equal('default', $parameters['lang']);

$parameters['lang'] = 'cs';
Assert::equal('cs', $parameters['lang']);
Assert::equal('default', $presenter->lang);
