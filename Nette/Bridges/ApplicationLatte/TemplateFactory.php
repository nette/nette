<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette,
	Nette\Application\UI;


/**
 * Latte powered template factory.
 *
 * @author     David Grudl
 */
class TemplateFactory extends Nette\Object implements UI\ITemplateFactory
{
	/** @var Nette\Bridges\Framework\ILatteFactory */
	private $latteFactory;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Security\User */
	private $user;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;


	public function __construct(Nette\Bridges\Framework\ILatteFactory $latteFactory, Nette\Http\IRequest $httpRequest = NULL,
		Nette\Http\IResponse $httpResponse = NULL, Nette\Security\User $user = NULL, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
	}


	/**
	 * @return Template
	 */
	public function createTemplate(UI\Control $control)
	{
		$latte = $this->latteFactory->create();
		$template = new Template($latte);
		$presenter = $control->getPresenter(FALSE);

		if ($control instanceof UI\Presenter) {
			$latte->setLoader(new Loader($control));
		}

		$latte->onCompile[] = function($latte) use ($control, $template) {
			$latte->getParser()->shortNoEscape = TRUE;
			$latte->getCompiler()->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($latte->getCompiler()));
			UIMacros::install($latte->getCompiler());
			Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());
			$control->templatePrepareFilters($template);
		};

		// default parameters
		$template->control = $template->_control = $control;
		$template->presenter = $template->_presenter = $presenter;
		$template->user = $this->user;
		$template->netteHttpResponse = $this->httpResponse;
		$template->netteCacheStorage = $this->cacheStorage;
		$template->baseUri = $template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		$template->flashes = array();

		if ($presenter instanceof UI\Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = (array) $presenter->getFlashSession()->$id;
		}

		return $template;
	}

}
