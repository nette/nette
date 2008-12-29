<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2008 John Doe
 * @package    MyApplication
 * @version    $Id$
 */



/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends /*Nette\Application\*/Presenter
{

	/**
	 * @return /*Nette\Templates\*/ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		return $template;
	}

}
