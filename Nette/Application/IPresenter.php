<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Presenter converts Request to IResponse.
 *
 * @author     David Grudl
 */
interface IPresenter
{

	/**
	 * @return IResponse
	 */
	function run(Request $request);

}
