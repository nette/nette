<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
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
 */
class MicroPresenter extends Nette\Object implements Application\IPresenter
{
	/** @var Nette\DI\IContainer */
	private $context;

	/** @var Nette\Application\Request */
	private $request;
	
	/** @var Nette\Templating\ITemplate */
	public $template;



	/**
	 * @param  Nette\Application\Request
	 * @return Nette\Application\IResponse
	 */
	public function run(Application\Request $request)
	{
		$this->request = $request;

		$httpRequest = $this->context->httpRequest;
		if (!$httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $httpRequest->getUrl();
			$url = $this->context->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url !== NULL && !$httpRequest->getUrl()->isEqual($url)) {
				return new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParams();
		if (!isset($params['callback'])) {
			return;
		}
		unset($params['callback']);
		$params['params'] = $params;
		$params['presenter'] = $this;
		$params['context'] = $this->context;
		$response = callback($request->params['callback'])->invokeNamedArgs($params);

		if (is_string($response)) {
			$response = array($response, array());
		}
		if (is_array($response)) {
			if ($response[0] instanceof \SplFileInfo) {
				$response = $this->createTemplate('Nette\Templating\FileTemplate')
						->setParams($response[1])->setFile($response[0]);
			} else {
				$response = $this->createTemplate('Nette\Templating\Template')
						->setParams($response[1])->setSource($response[0]);
			}
		}
		if ($response instanceof Nette\Templating\ITemplate) {
			return new Responses\TextResponse($response);
		} else {
			return $response;
		}
	}



	/**
	 * Template factory
	 * @param  string
	 * @param  callback
	 * @return Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL, $latteFactory = NULL)
	{
		if ($this->template) { //template has been already created in route callback
			return $this->template;
		}
		$template = $class ? new $class : new Nette\Templating\FileTemplate;

		$template->setParams($this->request->getParams());
 		$template->presenter = $this;
		$template->context = $context = $this->context;
		$url = $context->httpRequest->getUrl();
		$template->baseUrl = rtrim($url->getBaseUrl(), '/');
		$template->basePath = rtrim($url->getBasePath(), '/');

		$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');
		$template->setCacheStorage($context->templateCacheStorage);
		$template->onPrepareFilters[] = function($template) use ($latteFactory, $context) {
				$template->registerFilter($latteFactory ? $latteFactory() : new Nette\Latte\Engine);
			};
		$this->template = $template;
		return $template;
	}



	/**
	 * Redirects to another URL.
	 * @param  string
	 * @param  int HTTP code
	 * @return void
	 */
	public function redirectUrl($url, $code = Http\IResponse::S302_FOUND)
	{
		return new Responses\RedirectResponse($url, $code);
	}



	/**
	 * Throws HTTP error.
	 * @param  int HTTP error code
	 * @param  string
	 * @return void
	 * @throws Nette\Application\BadRequestException
	 */
	public function error($code, $message = NULL)
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

	/********************* services ****************d*g**/



	/**
	 * Gets the context.
	 * @return Presenter  provides a fluent interface
	 */
	public function setContext(Nette\DI\IContainer $context)
	{
		$this->context = $context;
		return $this;
	}



	/**
	 * Gets the context.
	 * @return Nette\DI\IContainer
	 */
	final public function getContext()
	{
		return $this->context;
	}

}
