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
 * Default presenter loader.
 *
 * @author     David Grudl
 */
class SimplePresenter implements Application\IPresenter
{
	/** @var Nette\DI\IContainer */
	private $context;

	/** @var Nette\Application\Request */
	private $request;



	/**
	 * @param  Nette\Application\Request
	 * @return Nette\Application\IResponse
	 */
	public function run(Application\Request $request)
	{
		$this->request = $request;
		$params = $request->getParams();
		if (isset($params['callback'])) {
			$params['presenter'] = $this;
			return callback($params['callback'])->invokeNamedArgs($params);
		}
	}



	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function redirectUrl($url, $code = NULL)
	{
		if (!$code) {
			$code = $this->context->httpRequest->isMethod('post')
				? Http\IResponse::S303_POST_GET
				: Http\IResponse::S302_FOUND;
		}
		return new Responses\RedirectResponse($url, $code);
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
