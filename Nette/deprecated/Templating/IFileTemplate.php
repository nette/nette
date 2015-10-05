<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Templating;


/**
 * @deprecated
 */
interface IFileTemplate extends ITemplate
{

	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return void
	 */
	function setFile($file);

	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	function getFile();

}
