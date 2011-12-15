<?php

/**
 * Test: Nette\Templating\TemplateFactory::createTemplate()
 *
 * @author     Patrik Votoček
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application,
	Nette\Environment,
	Nette\Templating\TemplateFactory;



require __DIR__ . '/../bootstrap.php';



class TestControl extends Application\UI\Control { }

class TestPresenter extends Application\UI\Presenter { }

$url = new Http\UrlScript('http://localhost/index.php');
$url->setScriptPath('/index.php');
$httpRequest = new Http\Request($url);
$httpResponse = Environment::getContext()->httpResponse;
$user = Environment::getContext()->user;
$cacheStorage = Environment::getContext()->cacheStorage;

$presenter = new TestPresenter(Environment::getContext());

$control = new TestControl($presenter, 'myControl');



$factory = new TemplateFactory($cacheStorage);
$factory->setHttpRequest($httpRequest);
$factory->setHttpResponse($httpResponse);
$factory->setUser($user);
$factory->setCacheStorage($cacheStorage);

$template = $factory->createTemplate($control);

Assert::true($template instanceof Nette\Templating\Template);

Assert::same($control, $template->_control);
Assert::same($presenter, $template->_presenter);

Assert::equal("http://localhost", $template->baseUrl);
Assert::equal("http://localhost", $template->baseUri);
Assert::equal("", $template->basePath);

Assert::same($cacheStorage, $template->netteCacheStorage);

Assert::same($httpResponse, $template->netteHttpResponse);

Assert::same($user, $template->user);
