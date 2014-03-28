<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * Templating engine Latte.
 *
 * @author     David Grudl
 */
class Engine extends Nette\Object
{
	/** Content types */
	const CONTENT_HTML = Compiler::CONTENT_HTML,
		CONTENT_XHTML = Compiler::CONTENT_XHTML,
		CONTENT_XML = Compiler::CONTENT_XML,
		CONTENT_JS = Compiler::CONTENT_JS,
		CONTENT_CSS = Compiler::CONTENT_CSS,
		CONTENT_ICAL = Compiler::CONTENT_ICAL,
		CONTENT_TEXT = Compiler::CONTENT_TEXT;

	/** @var array */
	public $onCompile;

	/** @var Parser */
	private $parser;

	/** @var Compiler */
	private $compiler;

	/** @var ILoader */
	private $loader;

	/** @var string */
	private $contentType = self::CONTENT_HTML;

	/** @var array run-time filters */
	private $filters = array(
		NULL => array(), // dynamic
	);

	/** @internal @var Nette\Caching\IStorage */
	public $cacheStorage;


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render($name, array $params = array())
	{
		if ($this->getLoader() instanceof Loaders\FileLoader) {
			$template = new Nette\Templating\FileTemplate($name);
		} else {
			$template = new Nette\Templating\Template;
			$template->setSource($name);
		}
		$template->registerFilter($this);
		$template->setParameters($params);
		foreach ($this->filters as $key => $callback) {
			$template->registerHelper($key, $callback);
		}
		foreach ($this->filters[NULL] as $callback) {
			$template->registerHelperLoader($callback);
		}
		if ($this->cacheStorage) {
			$template->setCacheStorage($this->cacheStorage);
		}
		$template->render();
	}


	/**
	 * Renders template to string.
	 * @return string
	 */
	public function renderToString($name, array $params = array())
	{
		ob_start();
		try {
			$this->render($name, $params);
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}
		return ob_get_clean();
	}


	/**
	 * Compiles template to PHP code.
	 * @return string
	 */
	public function compile($name)
	{
		if ($this->onCompile) {
			$this->onCompile($this);
			$this->onCompile = array();
		}
		$source = $this->getLoader()->getContent($name);
		$tokens = $this->getParser()->setContentType($this->contentType)
			->parse($source);
		$code = $this->getCompiler()->setContentType($this->contentType)
			->compile($tokens);
		$code = Helpers::optimizePhp($code);
		return $code;
	}


	/**
	 * Registers run-time filter.
	 * @param  string|NULL
	 * @param  callable
	 * @return self
	 */
	public function addFilter($name, $callback)
	{
		if ($name == NULL) { // intentionally ==
			array_unshift($this->filters[NULL], $callback);
		} else {
			$this->filters[strtolower($name)] = $callback;
		}
		return $this;
	}


	/**
	 * Returns all run-time filters.
	 * @return callable[]
	 */
	public function getFilters()
	{
		return $this->filters;
	}


	/**
	 * Adds new macro.
	 * @return self
	 */
	public function addMacro($name, IMacro $macro)
	{
		$this->getCompiler()->addMacro($name, $macro);
		return $this;
	}


	/**
	 * @return self
	 */
	public function setContentType($type)
	{
		$this->contentType = $type;
		return $this;
	}


	/**
	 * @deprecated
	 */
	public function __invoke($s)
	{
		return $this->setLoader(new Loaders\StringLoader)->compile($s);
	}


	/**
	 * @return Parser
	 */
	public function getParser()
	{
		if (!$this->parser) {
			$this->parser = new Parser;
		}
		return $this->parser;
	}


	/**
	 * @return Compiler
	 */
	public function getCompiler()
	{
		if (!$this->compiler) {
			$this->compiler = new Compiler;
			Macros\CoreMacros::install($this->compiler);
			$this->compiler->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($this->compiler));
			Macros\UIMacros::install($this->compiler);
			Nette\Bridges\FormsLatte\FormMacros::install($this->compiler);
		}
		return $this->compiler;
	}


	/**
	 * @return self
	 */
	public function setLoader(ILoader $loader)
	{
		$this->loader = $loader;
		return $this;
	}


	/**
	 * @return ILoader
	 */
	public function getLoader()
	{
		if (!$this->loader) {
			$this->loader = new Loaders\FileLoader;
		}
		return $this->loader;
	}

}
