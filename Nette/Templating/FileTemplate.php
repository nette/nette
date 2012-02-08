<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;

use Nette,
	Nette\Caching;



/**
 * Template stored in file.
 *
 * @author     David Grudl
 */
class FileTemplate extends Template implements IFileTemplate, ILayoutedTemplate
{
	/** @var string */
	private $file;



	/**
	 * Constructor.
	 * @param  string  template file path
	 */
	public function __construct($file = NULL)
	{
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}



	/**
	 * Returns array of all possible filenames.
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		extract($this->place);

		if (isset($this->control)) {
			$dir = dirname(dirname($this->control->getReflection()->getFileName()));

		} elseif (isset($this->presenter)) {
			$dir = dirname(dirname($this->presenter->getReflection()->getFileName()));

		} else {
			$dir = APP_DIR;
		}

		$files[] = "$dir/templates/$presenter/$view.latte";
		$files[] = "$dir/templates/$presenter.$view.latte";
		$files[] = "$dir/templates/$presenter/$view.phtml";
		$files[] = "$dir/templates/$presenter.$view.phtml";

		// layouts and includes
		$files[] = "$dir/templates/$view.latte";
		$files[] = "$dir/templates/$view.phtml";

		// inheritance of layouts
		if (substr($view, 0, 1) === '@') while ($module) {
			$dir = dirname($dir);
			$files[] = "$dir/templates/$view.latte";
			$files[] = "$dir/templates/$view.phtml";
			array_pop($module);
		}

		return $files;
	}



	/**
	 * Set, what we want to render, check if file exists.
	 * @param  string  $where  from presenter->name
	 * @param  string  $view
	 * @throws Nette\FileNotFoundException if no template found
	 */
	public function setPlace($where, $view)
	{
		parent::setPlace($where, $view);
		if ($view !== '@layout') {
			// file @layout.latte is optional
			$this->file = NULL;
		}

		$files = $this->formatTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				$this->setFile($file);
				break;
			}
		}

		if (!$this->getFile() && $view !== '@layout') {
			throw new Nette\FileNotFoundException("Missing template file '$files[0]'.");
		}
	}



	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return FileTemplate  provides a fluent interface
	 */
	public function setFile($file)
	{
		$this->file = realpath($file);
		if (!$this->file) {
			throw new Nette\FileNotFoundException("Missing template file '$file'.");
		}
		return $this;
	}



	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * Returns template source code.
	 * @return source
	 */
	public function getSource()
	{
		return file_get_contents($this->file);
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new Nette\InvalidStateException("Template file name was not specified.");
		}

		$cache = new Caching\Cache($storage = $this->getCacheStorage(), 'Nette.FileTemplate');
		if ($storage instanceof Caching\Storages\PhpFileStorage) {
			$storage->hint = str_replace(dirname(dirname($this->file)), '', $this->file);
		}
		$cached = $compiled = $cache->load($this->file);

		if ($compiled === NULL) {
			try {
				$compiled = "<?php\n\n// source file: $this->file\n\n?>" . $this->compile();

			} catch (FilterException $e) {
				$e->setSourceFile($this->file);
				throw $e;
			}

			$cache->save($this->file, $compiled, array(
				Caching\Cache::FILES => $this->file,
				Caching\Cache::CONSTS => 'Nette\Framework::REVISION',
			));
			$cached = $cache->load($this->file);
		}

		if ($cached !== NULL && $storage instanceof Caching\Storages\PhpFileStorage) {
			Nette\Utils\LimitedScope::load($cached['file'], $this->getParameters());
		} else {
			Nette\Utils\LimitedScope::evaluate($compiled, $this->getParameters());
		}
	}

}
