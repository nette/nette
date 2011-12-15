<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;

use Nette\Application\UI\Control;



/**
 * Define template factory methods.
 *
 * @author     Patrik Votoček
 */
interface ITemplateFactory
{
	/**
	 * @param  Nette\Application\UI\Control  application control or presenter
	 * @param  string   template class name
	 * @return ITemplate
	 */
	public function createTemplate(Control $control, $class = NULL);
}
