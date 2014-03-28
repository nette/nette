<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Defines ITemplate factory.
 */
interface ITemplateFactory
{

	/**
	 * @return ITemplate
	 */
	function createTemplate(Control $control);

}
