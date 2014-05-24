<?php

/**
 * Test: Nette\Application\PresenterFactory.
 */

use Nette\Application\PresenterFactory,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$factory = new PresenterFactory('base', new Nette\DI\Container);

test(function() use ($factory) {
	$factory->setMapping(array(
		'Foo2' => 'App2\*\*Presenter',
	));

	Assert::same( 'base/presenters/FooPresenter.php', $factory->formatPresenterFile('Foo') );
	Assert::same( 'base/FooModule/presenters/BarPresenter.php', $factory->formatPresenterFile('Foo:Bar') );
	Assert::same( 'base/FooModule/BarModule/presenters/BazPresenter.php', $factory->formatPresenterFile('Foo:Bar:Baz') );

	Assert::same( 'base/presenters/Foo2Presenter.php', $factory->formatPresenterFile('Foo2') );
	Assert::same( 'base/Foo2Module/presenters/BarPresenter.php', $factory->formatPresenterFile('Foo2:Bar') );
	Assert::same( 'base/Foo2Module/BarModule/presenters/BazPresenter.php', $factory->formatPresenterFile('Foo2:Bar:Baz') );
});


test(function() use ($factory) {
	$factory->setMapping(array(
		'Foo2' => 'App2\*Presenter',
	));

	Assert::same( 'base/presenters/Foo2Presenter.php', $factory->formatPresenterFile('Foo2') );
	Assert::same( 'base/Foo2Module/presenters/BarPresenter.php', $factory->formatPresenterFile('Foo2:Bar') );
	Assert::same( 'base/Foo2Module/BarModule/presenters/BazPresenter.php', $factory->formatPresenterFile('Foo2:Bar:Baz') );
});
