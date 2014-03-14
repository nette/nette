<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Control is renderable Presenter component.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Templating\ITemplate $template
 * @property-read string $snippetId
 */
abstract class Control extends PresenterComponent implements IRenderable
{
	/** @var Nette\Templating\ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = array();

	/** @var bool */
	public $snippetMode;


	/********************* template factory ****************d*g**/


	/**
	 * @return Nette\Templating\ITemplate
	 */
	public function getTemplate()
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!$value instanceof Nette\Templating\ITemplate && $value !== NULL) {
				$class2 = get_class($value); $class = get_class($this);
				throw new Nette\UnexpectedValueException("Object returned by $class::createTemplate() must be instance of Nette\\Templating\\ITemplate, '$class2' given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}


	/**
	 * @param  string|NULL
	 * @return Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = $class ? new $class : new Nette\Templating\FileTemplate;
		$presenter = $this->getPresenter(FALSE);
		$template->onPrepareFilters[] = $this->templatePrepareFilters;
		$template->registerHelperLoader('Nette\Latte\Runtime\Filters::loader');

		// default parameters
		$template->control = $template->_control = $this;
		$template->presenter = $template->_presenter = $presenter;
		if ($presenter instanceof Presenter) {
			$template->setCacheStorage($presenter->getContext()->getService('nette.templateCacheStorage'));
			$template->user = $presenter->getUser();
			$template->netteHttpResponse = $presenter->getHttpResponse();
			$template->netteCacheStorage = $presenter->getContext()->getByType('Nette\Caching\IStorage');
			$template->baseUri = $template->baseUrl = rtrim($presenter->getHttpRequest()->getUrl()->getBaseUrl(), '/');
			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);

			// flash message
			if ($presenter->hasFlashSession()) {
				$id = $this->getParameterId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
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
		$template->registerFilter($this->getPresenter()->getContext()->createService('nette.latte'));
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 * @param  string
	 * @param  string
	 * @return \stdClass
	 */
	public function flashMessage($message, $type = 'info')
	{
		$id = $this->getParameterId('flash');
		$messages = $this->getPresenter()->getFlashSession()->$id;
		$messages[] = $flash = (object) array(
			'message' => $message,
			'type' => $type,
		);
		$this->getTemplate()->flashes = $messages;
		$this->getPresenter()->getFlashSession()->$id = $messages;
		return $flash;
	}


	/********************* rendering ****************d*g**/


	/**
	 * Forces control or its snippet to repaint.
	 * @return void
	 */
	public function redrawControl($snippet = NULL, $redraw = TRUE)
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet] = TRUE;

		} elseif ($snippet === NULL) {
			$this->invalidSnippets = array();

		} else {
			unset($this->invalidSnippets[$snippet]);
		}
	}


	/** @deprecated */
	function invalidateControl($snippet = NULL)
	{
		$this->redrawControl($snippet);
	}

	/** @deprecated */
	function validateControl($snippet = NULL)
	{
		$this->redrawControl($snippet, FALSE);
	}


	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 * @return bool
	 */
	public function isControlInvalid($snippet = NULL)
	{
		if ($snippet === NULL) {
			if (count($this->invalidSnippets) > 0) {
				return TRUE;

			} else {
				$queue = array($this);
				do {
					foreach (array_shift($queue)->getComponents() as $component) {
						if ($component instanceof IRenderable) {
							if ($component->isControlInvalid()) {
								// $this->invalidSnippets['__child'] = TRUE; // as cache
								return TRUE;
							}

						} elseif ($component instanceof Nette\ComponentModel\IContainer) {
							$queue[] = $component;
						}
					}
				} while ($queue);

				return FALSE;
			}

		} else {
			return isset($this->invalidSnippets[NULL]) || isset($this->invalidSnippets[$snippet]);
		}
	}


	/**
	 * Returns snippet HTML ID.
	 * @param  string  snippet name
	 * @return string
	 */
	public function getSnippetId($name = NULL)
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}

}
