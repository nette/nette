<?php

/**
 * Test: Nette\Application\PresenterFactory.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @phpversion 5.3
 */

use Nette\Application\PresenterFactory;



require __DIR__ . '/../bootstrap.php';



$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$factory = new PresenterFactory(NULL, $container);

$factory->mapping['Foo2'] = 'App2\*\*Presenter';
$factory->mapping['Foo3'] = 'My\App\*Mod\*Presenter';

Assert::same( 'FooPresenter', $factory->formatPresenterClass('Foo') );
Assert::same( 'FooModule\BarPresenter', $factory->formatPresenterClass('Foo:Bar') );
Assert::same( 'FooModule\BarModule\BazPresenter', $factory->formatPresenterClass('Foo:Bar:Baz') );

Assert::same( 'Foo2Presenter', $factory->formatPresenterClass('Foo2') );
Assert::same( 'App2\BarPresenter', $factory->formatPresenterClass('Foo2:Bar') );
Assert::same( 'App2\Bar\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz') );

Assert::same( 'My\App\BarPresenter', $factory->formatPresenterClass('Foo3:Bar') );
Assert::same( 'My\App\BarMod\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz') );

Assert::same( 'NetteModule\FooPresenter', $factory->formatPresenterClass('Nette:Foo') );
