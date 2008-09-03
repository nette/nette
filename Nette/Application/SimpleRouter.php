<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/IRouter.php';



/**
 * The bidirectional route for trivial routing via query string.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
class SimpleRouter extends /*Nette::*/Object implements IRouter
{
	const PRESENTER_KEY = 'presenter';
	const MODULE_KEY = 'module';

	/** @var string */
	protected $module = '';

	/** @var array */
	protected $defaults;

	/** @var int */
	protected $flags;



	/**
	 * @param  array   default values
	 * @param  int     flags
	 */
	public function __construct(array $defaults = array(), $flags = 0)
	{
		if (isset($defaults[self::MODULE_KEY])) {
			$this->module = $defaults[self::MODULE_KEY] . ':';
			unset($defaults[self::MODULE_KEY]);
		}

		$this->defaults = $defaults;
		$this->flags = $flags;
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette::Web::IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(/*Nette::Web::*/IHttpRequest $context)
	{
		// combine with precedence: get, (post,) defaults
		$params = $context->getQuery();
		//$params += $context->getPost();
		$params += $this->defaults;

		if (isset($params[self::PRESENTER_KEY])) {
			$presenter = $this->module . $params[self::PRESENTER_KEY];
			unset($params[self::PRESENTER_KEY]);
		}

		return new PresenterRequest(
			$presenter,
			$context->getMethod() === 'POST' ? PresenterRequest::HTTP_POST : PresenterRequest::HTTP_GET,
			$params,
			$context->getPost(),
			$context->getFiles()
		);
	}



	/**
	 * Constructs URL path from PresenterRequest object.
	 * @param  Nette::Web::IHttpRequest
	 * @param  PresenterRequest
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $request, /*Nette::Web::*/IHttpRequest $context)
	{
		$params = $request->getParams();

		// presenter name
		$presenter = $request->getPresenterName();
		if (strncasecmp($presenter, $this->module, strlen($this->module)) === 0) {
			$params[self::PRESENTER_KEY] = substr($presenter, strlen($this->module));
		} else {
			return NULL;
		}

		// remove default values; NULL values are retain
		foreach ($this->defaults as $key => $value) {
			if (isset($params[$key]) && $params[$key] == $value) { // intentionally ==
				unset($params[$key]);
			}
		}

		$uri = $context->getUri()->scriptPath;
		$query = http_build_query($params, '', '&');
		if ($query !== '') {
			$uri .= '?' . $query;
		}

		if ($this->flags & self::SECURED) {
			$uri = 'https://' . $context->getUri()->authority . $uri;
		}

		return $uri;
	}

}