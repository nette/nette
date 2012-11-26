<?php

/**
 * Test: Nette\Config\Extension\NetteExtension: template factory is configured
 *
 * @author     Filip Prochazka
 * @package    Nette\Config\Extension
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';


class PresenterStub extends Nette\Application\UI\Presenter
{

}



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->createContainer();

$app = $container->application;
$appRefl = $app->getReflection()->getProperty('presenter');
$appRefl->setAccessible(TRUE);
$appRefl->setValue($app, new PresenterStub);

$templateFactory = $container->getByType('Nette\Templating\ITemplateFactory');
Assert::true( $templateFactory->create() instanceof Nette\Templating\FileTemplate );
Assert::true( $templateFactory->create('Nette\Templating\Template') instanceof Nette\Templating\Template );

$template = $templateFactory->create();

$filters = $template->getFilters();
Assert::true( $filters[0]->getNative() instanceof Nette\Latte\Engine );

$helperLoaders = $template->getHelperLoaders();
Assert::same( $helperLoaders[0]->getNative(), 'Nette\Templating\Helpers::loader' );

Assert::same( $template->getCacheStorage(), $container->getService('nette.templateCacheStorage') );
Assert::true( $template->presenter instanceof Nette\Application\IPresenter );
Assert::true( $template->user instanceof Nette\Security\User );
Assert::true( $template->netteHttpResponse instanceof Nette\Http\IResponse );
Assert::true( $template->netteCacheStorage instanceof Nette\Caching\IStorage );
Assert::true( isset($template->baseUri) );
Assert::true( isset($template->baseUrl) );
Assert::true( isset($template->basePath) );
