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

use Nette,
	Nette\Application\UI\Control,
	Nette\Application\UI\Presenter;



/**
 * Responsible for creating a new instance of template.
 *
 * @author     Patrik Votoček
 */
class TemplateFactory extends Nette\Object implements ITemplateFactoryFilesFormatter
{
	/** @var Nette\Caching\IStorage */
	private $templateCacheStorage;
	/** @var Nette\Http\IUser */
	private $user;
	/** @var Nette\Http\IResponse */
	private $httpResponse;
	/** @var Nette\Http\IRequest */
	private $httpRequest;
	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	public function __construct(Nette\Caching\IStorage $templateCacheStorage = NULL)
	{
		$this->templateCacheStorage = $templateCacheStorage;
	}

	public function setUser(Nette\Http\IUser $user)
	{
		$this->user = $user;
		return $this;
	}

	public function setHttpResponse(Nette\Http\IResponse $httpResponse)
	{
		$this->httpResponse = $httpResponse;
		return $this;
	}

	public function setHttpRequest(Nette\Http\IRequest $httpRequest)
	{
		$this->httpRequest = $httpRequest;
		return $this;
	}

	public function setCacheStorage(Nette\Caching\IStorage $cacheStorage)
	{
		$this->cacheStorage = $cacheStorage;
		return $this;
	}

	/**
	 * @param  Nette\Application\UI\Control  application control or presenter
	 * @param  string   template class name
	 * @return ITemplate
	 */
	public function createTemplate(Control $control, $class = NULL)
	{
		$template = $class ? new $class : new FileTemplate;
		return $this->configureTemplate($template, $control);
	}

	/**
	 * @param  ITemplate  template to configure
	 * @param  Nette\Application\UI\Control  application control or presenter
	 * @return ITemplate
	 */
	protected function configureTemplate(ITemplate $template, Control $control)
	{
		$presenter = $control->getPresenter(FALSE);
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');
		$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

		// default parameters
		$template->control = $template->_control = $control;
		$template->presenter = $template->_presenter = $presenter;
		if ($this->templateCacheStorage) {
			$template->setCacheStorage($this->templateCacheStorage);
		}
		if ($this->cacheStorage) {
			$template->netteCacheStorage = $this->cacheStorage;
		}
		if ($this->httpResponse) {
			$template->netteHttpResponse = $this->httpResponse;
		}
		if ($this->httpRequest) {
			$template->baseUri = $template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		}
		if ($this->user) {
			$template->user = $this->user;
		}
		// flash message
		if ($presenter instanceof Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = $presenter->getFlashSession()->$id;
		}

		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}

	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  Nette\Templating\Template
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		$template->registerFilter(new Nette\Latte\Engine);
	}

	/**
	 * Formats layout template file names.
	 * @param Nette\Application\UI\Control
	 * @param string   layout name
	 * @return array
	 */
	public function formatLayoutTemplateFiles(Control $control, $layout = 'layout')
	{
		$name = $control->getName();
		$controlName = substr($name, strrpos(':' . $name, ':'));
		$layout = $layout ? $layout : 'layout';
		$dir = dirname(dirname($control->getReflection()->getFileName()));
		$list = array(
			"$dir/templates/$controlName/@$layout.latte",
			"$dir/templates/$controlName.@$layout.latte",
			"$dir/templates/$controlName/@$layout.phtml",
			"$dir/templates/$controlName.@$layout.phtml",
		);
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$list[] = "$dir/templates/@$layout.phtml";
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
		return $list;
	}



	/**
	 * Formats view template file names.
	 * @param Nette\Application\UI\Control
	 * @param string   layout name
	 * @return array
	 */
	public function formatTemplateFiles(Control $control, $view)
	{
		$name = $control->getName();
		$controlName = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname(dirname($control->getReflection()->getFileName()));
		return array(
			"$dir/templates/$controlName/$view.latte",
			"$dir/templates/$controlName.$view.latte",
			"$dir/templates/$controlName/$view.phtml",
			"$dir/templates/$controlName.$view.phtml",
		);
	}
}
