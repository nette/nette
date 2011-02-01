<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * Defines method that must be implemented to allow a component to act like a presenter.
 *
 * @author     David Grudl
 */
interface IPresenter
{

	/**
	 * @param  PresenterRequest
	 * @return IPresenterResponse
	 */
	function run(PresenterRequest $request);

}
