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
	/** @var Nette\DI\Container|NULL */
	private $context;

	/** @var Nette\Http\IRequest|NULL */
	private $httpRequest;

	/** @var IRouter|NULL */
	private $router;

	/** @var Request */
	private $request;


	public function __construct(Nette\DI\Container $context = NULL, Http\IRequest $httpRequest = NULL, IRouter $router = NULL)
	{
		$this->context = $context;
		$this->httpRequest = $httpRequest;
		$this->router = $router;
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

		if ($this->httpRequest && $this->router && !$this->httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $this->httpRequest->getUrl();
			$url = $this->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url !== NULL && !$this->httpRequest->getUrl()->isEqual($url)) {
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

		if ($this->context) {
			$params = Nette\DI\Helpers::autowireArguments($reflection, $params, $this->context);
		}

		$response = call_user_func_array($callback, $params);

		if (is_string($response)) {
			$response = array($response, array());
		}
		if (is_array($response)) {
			$response = $this->createTemplate()->setParameters($response[1]);
			if ($response[0] instanceof \SplFileInfo) {
				$response->setFile($response[0]);
			} else {
				$response->setSource($response[0]); // TODO
			}
		}
		if ($response instanceof Application\UI\ITemplate) {
			return new Responses\TextResponse($response);
		} else {
			return $response;
		}
	}


	/**
	 * Template factory.
	 * @param  string
	 * @param  callable
	 * @return Application\UI\ITemplate
	 */
	public function createTemplate($class = NULL, $latteFactory = NULL)
	{
		$latte = $latteFactory ? $latteFactory() : $this->getContext()->getByType('Nette\Bridges\Framework\ILatteFactory')->create();
		$template = $class ? new $class : new Nette\Bridges\ApplicationLatte\Template($latte);

		$template->setParameters($this->request->getParameters());
		$template->presenter = $this;
		$template->context = $this->context;
		if ($this->httpRequest) {
			$url = $this->httpRequest->getUrl();
			$template->baseUrl = rtrim($url->getBaseUrl(), '/');
			$template->basePath = rtrim($url->getBasePath(), '/');
		}
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
