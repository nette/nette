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

use Nette\Application\UI\Control,
	Nette\Application\UI\Presenter;



/**
 * Define template factory with file formaters methods.
 *
 * @author     Patrik Votoček
 */
interface ITemplateFactoryFilesFormatter extends ITemplateFactory
{
	/**
	 * Formats layout template file names.
	 * @param  Nette\Application\UI\Control  application control or presenter
	 * @param  string   layout name
	 * @return array
	 */
	public function formatLayoutTemplateFiles(Control $control, $layout);

	/**
	 * Formats view template file names.
	 * @param  Nette\Application\UI\Control  application control or presenter
	 * @param  string   view name
	 * @return array
	 */
	public function formatTemplateFiles(Control $control, $view);
}
