<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace NetteModule;

use Nette,
	Nette\Application,
	Nette\Application\Responses,
	Nette\Http;


/**
 * Micro presenter.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Application\IRequest $request
 */
class MicroPresenter extends Nette\Object implements Application\IPresenter
{
	/** @var Nette\DI\Container */
	private $context;

	/** @var Nette\Application\Request */
	private $request;


	public function __construct(Nette\DI\Container $context)
	{
		$this->context = $context;
	}


	/**
	 * Gets the context.
	 * @return \SystemContainer|Nette\DI\Container
	 */
	public function getContext()
	{
		return $this->context;
	}


	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(Application\Request $request)
	{
		$this->request = $request;

		$httpRequest = $this->context->getByType('Nette\Http\IRequest');
		if (!$httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $httpRequest->getUrl();
			$url = $this->context->getByType('Nette\Application\IRouter')->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url !== NULL && !$httpRequest->getUrl()->isEqual($url)) {
				return new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParameters();
		if (!isset($params['callback'])) {
			throw new Application\BadRequestException("Parameter callback is missing.");
		}
		$params['presenter'] = $this;
		$callback = $params['callback'];
		$reflection = Nette\Utils\Callback::toReflection(Nette\Utils\Callback::check($callback));
		$params = Application\UI\PresenterComponentReflection::combineArgs($reflection, $params);

		foreach ($reflection->getParameters() as $param) {
			if ($param->getClassName()) {
				unset($params[$param->getPosition()]);
			}
		}
		$params = Nette\DI\Helpers::autowireArguments($reflection, $params, $this->context);

		$response = call_user_func_array($callback, $params);

		if (is_string($response)) {
			$response = array($response, array());
		}
		if (is_array($response)) {
			if ($response[0] instanceof \SplFileInfo) {
				$response = $this->createTemplate('Nette\Templating\FileTemplate')
					->setParameters($response[1])->setFile($response[0]);
			} else {
				$response = $this->createTemplate('Nette\Templating\Template')
					->setParameters($response[1])->setSource($response[0]);
			}
		}
		if ($response instanceof Nette\Templating\ITemplate) {
			return new Responses\TextResponse($response);
		} else {
			return $response;
		}
	}


	/**
	 * Template factory.
	 * @param  string
	 * @param  callable
	 * @return Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL, $latteFactory = NULL)
	{
		$template = $class ? new $class : new Nette\Templating\FileTemplate;

		$template->setParameters($this->request->getParameters());
		$template->presenter = $this;
		$template->context = $context = $this->context;
		$url = $context->getByType('Nette\Http\IRequest')->getUrl();
		$template->baseUrl = rtrim($url->getBaseUrl(), '/');
		$template->basePath = rtrim($url->getBasePath(), '/');

		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->setCacheStorage($context->getService('nette.templateCacheStorage'));
		$template->onPrepareFilters[] = function($template) use ($latteFactory) {
			$template->registerFilter($latteFactory ? $latteFactory() : new Nette\Latte\Engine);
		};
		return $template;
	}


	/**
	 * Redirects to another URL.
	 * @param  string
	 * @param  int HTTP code
	 * @return Nette\Application\Responses\RedirectResponse
	 */
	public function redirectUrl($url, $code = Http\IResponse::S302_FOUND)
	{
		return new Responses\RedirectResponse($url, $code);
	}


	/**
	 * Throws HTTP error.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws Nette\Application\BadRequestException
	 */
	public function error($message = NULL, $code = Http\IResponse::S404_NOT_FOUND)
	{
		throw new Application\BadRequestException($message, $code);
	}


	/**
	 * @return Nette\Application\IRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}

}
