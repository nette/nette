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
	const CONTENT_HTML = 'html',
		CONTENT_XHTML = 'xhtml',
		CONTENT_XML = 'xml',
		CONTENT_JS = 'js',
		CONTENT_CSS = 'css',
		CONTENT_ICAL = 'ical',
		CONTENT_TEXT = 'text';

	/** @var Parser */
	private $parser;

	/** @var Compiler */
	private $compiler;

	/** @var ILoader */
	private $loader;

	/** @var array run-time filters */
	private $filters = array();

	/** @var array */
	private $filterLoaders = array();

	/** @internal @var Nette\Caching\IStorage */
	public $cacheStorage;


	public function __construct()
	{
		$this->loader = new Loaders\FileLoader;
		$this->parser = new Parser;
		$this->compiler = new Compiler;
		$this->compiler->defaultContentType = Compiler::CONTENT_HTML;

		Macros\CoreMacros::install($this->compiler);
		$this->compiler->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($this->compiler));
		Macros\UIMacros::install($this->compiler);
		Nette\Bridges\FormsLatte\FormMacros::install($this->compiler);
	}


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render($name, array $params = array())
	{
		if ($this->loader instanceof Loaders\FileLoader) {
			$template = new Nette\Templating\FileTemplate($name);
		} else {
			$template = new Nette\Templating\Template;
			$template->setSource($name);
		}
		$template->registerFilter($this);
		$template->setParameters($params);
		foreach ($this->filters as $name => $callback) {
			$template->registerHelper($name, $callback);
		}
		foreach ($this->filterLoaders as $callback) {
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
		$this->render($name, $params);
		return ob_get_clean();
	}


	/**
	 * Compiles template to PHP code.
	 * @return string
	 */
	public function compile($name)
	{
		if ($this->loader instanceof Loaders\FileLoader) {
			$template = new Nette\Templating\FileTemplate($name);
		} else {
			$template = new Nette\Templating\Template;
			$template->setSource($name);
		}
		$template->registerFilter($this);
		return $template->compile();
	}


	/**
	 * Registers callback as template run-time filter.
	 * @param  string
	 * @param  callable
	 * @return self
	 */
	public function addFilter($name, $callback)
	{
		$this->filters[strtolower($name)] = $callback;
		return $this;
	}


	/**
	 * Registers callback as template run-time filters loader.
	 * @param  callable
	 * @return self
	 */
	public function addFilterLoader($callback)
	{
		array_unshift($this->filterLoaders, $callback);
		return $this;
	}


	/**
	 * @return self
	 */
	public function setContentType($type)
	{
		$this->compiler->defaultContentType = $type;
		return $this;
	}


	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		return $this->compiler->compile($this->parser->parse($s));
	}


	/**
	 * @return Parser
	 */
	public function getParser()
	{
		return $this->parser;
	}


	/**
	 * @return Compiler
	 */
	public function getCompiler()
	{
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
		return $this->loader;
	}

}
