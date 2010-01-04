<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/

/*use Nette\Environment;*/
/*use Nette\Templates\LatteMacros;*/



/**
 * Presenter compatibility with Nette 0.8
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 * @deprecated
 */
abstract class OldPresenter extends Presenter
{
	/** @var bool */
	public $oldLayoutMode = TRUE;

	/** @var bool */
	public $oldModuleMode = TRUE;



	/**
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		LatteMacros::$defaultMacros = /*Nette\Templates\*/OldLatteMacros::$defaultMacros + LatteMacros::$defaultMacros;
		$filter = new /*Nette\Templates\*/LatteFilter;
		$template->registerFilter($filter->setHandler(new /*Nette\Templates\*/OldLatteMacros));
	}



	/**
	 * @return void
	 * @throws BadSignalException
	 */
	public function processSignal()
	{
		// beforePrepare & prepare<View>
		if (method_exists($this, 'beforePrepare')) {
			$this->beforePrepare();
			trigger_error('beforePrepare() is deprecated; use createComponent{Name}() instead.', E_USER_WARNING);
		}
		if ($this->tryCall('prepare' . $this->getView(), $this->params)) {
			trigger_error('prepare' . ucfirst($this->getView()) . '() is deprecated; use createComponent{Name}() instead.', E_USER_WARNING);
		}

		// auto invalidate
		list($signalReceiver, $signal) = $this->getSignal();
		if ($signal !== NULL && $this->oldLayoutMode) {
			$component = $signalReceiver === '' ? $this : $this->getComponent($signalReceiver, FALSE);
			if ($component instanceof IRenderable) {
				$component->invalidateControl();
			}
		}
		parent::processSignal();
	}



	/**
	 * @return void
	 * @throws BadRequestException if no template found
	 * @throws AbortException
	 */
	public function sendTemplate()
	{
		$template = $this->getTemplate();
		if (!$template) return;

		if ($template instanceof /*Nette\Templates\*/IFileTemplate && !$template->getFile()) {

			// content template
			$files = $this->formatTemplateFiles($this->getName(), $this->view);
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = str_replace(Environment::getVariable('appDir'), "\xE2\x80\xA6", reset($files));
				throw new BadRequestException("Page not found. Missing template '$file'.");
			}

			// layout template
			if ($this->layout !== FALSE) {
				$files = $this->formatLayoutTemplateFiles($this->getName(), $this->layout ? $this->layout : 'layout');
				foreach ($files as $file) {
					if (is_file($file)) {
						$template->layout = $file;
						if ($this->oldLayoutMode) {
							$template->content = clone $template;
							$template->setFile($file);
						} else {
							$template->_extends = $file;
						}
						break;
					}
				}

				if (empty($template->layout) && $this->layout !== NULL) {
					$file = str_replace(Environment::getVariable('appDir'), "\xE2\x80\xA6", reset($files));
					throw new /*\*/FileNotFoundException("Layout not found. Missing template '$file'.");
				}
			}
		}

		$this->terminate(new RenderResponse($template));
	}



	/**
	 * Formats layout template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{
		if ($this->oldModuleMode) {
			$root = Environment::getVariable('templatesDir', Environment::getVariable('appDir') . '/templates'); // back compatibility
			$presenter = str_replace(':', 'Module/', $presenter);
			$module = substr($presenter, 0, (int) strrpos($presenter, '/'));
			$base = '';
			if ($root === Environment::getVariable('appDir') . '/presenters') {
				$base = 'templates/';
				if ($module === '') {
					$presenter = 'templates/' . $presenter;
				} else {
					$presenter = substr_replace($presenter, '/templates', strrpos($presenter, '/'), 0);
				}
			}
			return array(
				"$root/$presenter/@$layout.phtml",
				"$root/$presenter.@$layout.phtml",
				"$root/$module/$base@$layout.phtml",
				"$root/$base@$layout.phtml",
			);
		}

		return parent::formatLayoutTemplateFiles($presenter, $layout);
	}



	/**
	 * Formats view template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public function formatTemplateFiles($presenter, $view)
	{
		if ($this->oldModuleMode) {
			$root = Environment::getVariable('templatesDir', Environment::getVariable('appDir') . '/templates'); // back compatibility
			$presenter = str_replace(':', 'Module/', $presenter);
			$dir = '';
			if ($root === Environment::getVariable('appDir') . '/presenters') { // special supported case
				$pos = strrpos($presenter, '/');
				$presenter = $pos === FALSE ? 'templates/' . $presenter : substr_replace($presenter, '/templates', $pos, 0);
				$dir = 'templates/';
			}
			return array(
				"$root/$presenter/$view.phtml",
				"$root/$presenter.$view.phtml",
				"$root/$dir@global.$view.phtml",
			);
		}

		return parent::formatTemplateFiles($presenter, $view);
	}



	/**
	 * @deprecated
	 */
	protected function renderTemplate()
	{
		throw new /*\*/DeprecatedException(__METHOD__ . '() is deprecated; use $presenter->sendTemplate() instead.');
	}



	/**
	 * @deprecated
	 */
	public function getAjaxDriver()
	{
		throw new /*\*/DeprecatedException(__METHOD__ . '() is deprecated; use $presenter->payload instead.');
	}

}
