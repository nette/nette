<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Templating;

use Nette,
	Nette\Caching,
	Latte;


/**
 * @deprecated
 */
class FileTemplate extends Template implements IFileTemplate
{
	/** @var string */
	private $file;


	/**
	 * Constructor.
	 * @param  string  template file path
	 */
	public function __construct($file = NULL)
	{
		//trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}


	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return self
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
	 * @return string
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
			throw new Nette\InvalidStateException('Template file name was not specified.');
		}

		if (!$this->getFilters()) {
			$this->onPrepareFilters($this);
		}

		if ($latte = $this->getLatte()) {
			return $latte->setLoader(new Latte\Loaders\FileLoader)->render($this->file, $this->getParameters());
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
				throw $e->setSource(file_get_contents($this->file), $e->sourceLine, $this->file);
			}

			$cache->save($this->file, $compiled, array(
				Caching\Cache::FILES => $this->file,
				Caching\Cache::CONSTS => 'Nette\Framework::REVISION',
			));
			$cached = $cache->load($this->file);
		}

		$isFile = $cached !== NULL && $storage instanceof Caching\Storages\PhpFileStorage;
		self::load($isFile ? $cached['file'] : $compiled, $this->getParameters(), $isFile);
	}

}
