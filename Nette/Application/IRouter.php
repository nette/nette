<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * The bi-directional router.
 *
 * @author     David Grudl
 */
interface IRouter
{
	/** only matching route */
	const ONE_WAY = 1;

	/** HTTPS route */
	const SECURED = 2;

	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	function match(Nette\Web\IHttpRequest $httpRequest);

	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * @param  PresenterRequest
	 * @param  Nette\Web\Uri referential URI
	 * @return string|NULL
	 */
	function constructUrl(PresenterRequest $appRequest, Nette\Web\Uri $refUri);

}
