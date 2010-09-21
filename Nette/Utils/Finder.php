<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette;

use Nette,
	RecursiveIteratorIterator;



/**
 * Finder allows searching through directory trees using iterator.
 *
 * Example: Finder::findFiles('*.php')
 *     ->size('> 10kB')
 *     ->from('.')
 *     ->exclude('temp');
 *
 * @author     David Grudl
 */
class Finder extends Object implements \IteratorAggregate
{
	/** @var array */
	private $paths = array();

	/** @var array of filters */
	private $groups;

	/** @var filter for recursive traversing */
	private $exclude = array();

	/** @var int */
	private $order = RecursiveIteratorIterator::SELF_FIRST;

	/** @var int */
	private $maxDepth = -1;

	/** @var array */
	private $cursor;



	/**
	 * Begins search for files matching mask and all directories.
	 * @param  mixed
	 * @return Finder
	 */
	public static function find($mask)
	{
		if (!is_array($mask)) {
			$mask = func_get_args();
		}
		$finder = new self;
		return $finder->select(array(), 'dir')->select($mask, 'file');
	}



	/**
	 * Begins search for files matching mask.
	 * @param  mixed
	 * @return Finder
	 */
	public static function findFiles($mask)
	{
		if (!is_array($mask)) {
			$mask = func_get_args();
		}
		$finder = new self;
		return $finder->select($mask, 'file');
	}



	/**
	 * Begins search for directories matching mask.
	 * @param  mixed
	 * @return Finder
	 */
	public static function findDirectories($mask)
	{
		if (!is_array($mask)) {
			$mask = func_get_args();
		}
		$finder = new self;
		return $finder->select($mask, 'dir');
	}



	/**
	 * Creates filtering group by mask & type selector.
	 * @param  array
	 * @param  string
	 * @return Finder  provides a fluent interface
	 */
	private function select($masks, $type)
	{
		$this->cursor = & $this->groups[];
		$pattern = self::buildPattern($masks);
		if ($type || $pattern) {
			$this->filter(function($file) use ($type, $pattern) {
				return (!$type || $file->getType() === $type)
					&& (!$pattern || preg_match($pattern, '/' . strtr($file->getSubPathName(), '\\', '/')));
			});
		}
		return $this;
	}



	/**
	 * Searchs in the given folder(s).
	 * @param  string|array
	 * @return Finder  provides a fluent interface
	 */
	public function in($path)
	{
		if (!is_array($path)) {
			$path = func_get_args();
		}
		$this->maxDepth = 0;
		return $this->from($path);
	}



	/**
	 * Searchs recursively from the given folder(s).
	 * @param  string|array
	 * @return Finder  provides a fluent interface
	 */
	public function from($path)
	{
		if ($this->paths) {
			throw new \InvalidStateException('Directory to search is already specified.');
		}
		if (!is_array($path)) {
			$path = func_get_args();
		}
		$this->paths = $path;
		$this->cursor = & $this->exclude;
		return $this;
	}



	/**
	 * Shows folder content prior to the folder.
	 * @return Finder  provides a fluent interface
	 */
	public function childFirst()
	{
		$this->order = RecursiveIteratorIterator::CHILD_FIRST;
		return $this;
	}



	/**
	 * Converts Finder pattern to regular expression.
	 * @param  array
	 * @return string
	 */
	private static function buildPattern($masks)
	{
		$pattern = array();
		// TODO: accept regexp
		foreach ($masks as $mask) {
			$mask = rtrim(strtr($mask, '\\', '/'), '/');
			$prefix = '';
			if ($mask === '') {
				continue;

			} elseif ($mask === '*') {
				return NULL;

			} elseif ($mask[0] === '/') { // absolute fixing
				$mask = ltrim($mask, '/');
				$prefix = '(?<=^/)';
			}
			$pattern[] = $prefix . strtr(preg_quote($mask, '#'),
				array('\*\*' => '.*', '\*' => '[^/]*', '\?' => '[^/]', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\-' => '-'));
		}
		return $pattern ? '#/(' . implode('|', $pattern) . ')$#i' : NULL;
	}



	/********************* iterator generator ****************d*g**/



	/**
	 * Returns iterator.
	 * @return Iterator
	 */
	public function getIterator()
	{
		if (!$this->paths) {
			throw new \InvalidStateException('Call in() or from() to specify directory to search.');

		} elseif (count($this->paths) === 1) {
			return $this->buildIterator($this->paths[0]);

		} else {
			$iterator = new \AppendIterator(); // buggy!
			foreach ($this->paths as $path) {
				$iterator->append($this->buildIterator($path));
			}
			return $iterator;
		}
	}



	/**
	 * Returns per-path iterator.
	 * @param  string
	 * @return Iterator
	 */
	private function buildIterator($path)
	{
		$iterator = new \RecursiveDirectoryIterator($path);

		if ($this->exclude) {
			$filters = $this->exclude;
			$iterator = new RecursiveCallbackFilterIterator($iterator, function($file) use ($filters) {
				if (!$file->isFile()) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							return FALSE;
						}
					}
				}
				return TRUE;
			});
		}

		if ($this->maxDepth !== 0) {
			$iterator = new RecursiveIteratorIterator($iterator, $this->order);
			$iterator->setMaxDepth($this->maxDepth);
		}

		if ($this->groups) {
			$groups = $this->groups;
			$iterator = new CallbackFilterIterator($iterator, function($file) use ($groups) {
				foreach ($groups as $filters) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							continue 2;
						}
					}
					return TRUE;
				}
				return FALSE;
			});
		}

		return $iterator;
	}



	/********************* filtering ****************d*g**/



	/**
	 * Restricts the search using mask.
	 * Excludes directories from recursive traversing.
	 * @param  mixed
	 * @return Finder  provides a fluent interface
	 */
	public function exclude($masks)
	{
		if (!is_array($masks)) {
			$masks = func_get_args();
		}
		$pattern = self::buildPattern($masks);
		if ($pattern) {
			$this->filter(function($file) use ($pattern) {
				return !preg_match($pattern, '/' . strtr($file->getSubPathName(), '\\', '/'));
			});
		}
		return $this;
	}



	/**
	 * Restricts the search using callback.
	 * @param  callback
	 * @return Finder  provides a fluent interface
	 */
	public function filter($callback)
	{
		$this->cursor[] = $callback;
		return $this;
	}



	/**
	 * Limits recursion level.
	 * @param  int
	 * @return Finder  provides a fluent interface
	 */
	public function limitDepth($depth)
	{
		$this->maxDepth = $depth;
		return $this;
	}



	/**
	 * Restricts the search by size.
	 * @param  string
	 * @param  int
	 * @return Finder  provides a fluent interface
	 */
	public function size($operator, $size)
	{
		return $this->filter(function($file) use ($operator, $size) {
			return Tools::compare($file->getSize(), $operator, $size);
		});
	}



	/**
	 * Restricts the search by modified time.
	 * @param  string
	 * @param  mixed
	 * @return Finder  provides a fluent interface
	 */
	public function date($operator, $date)
	{
		$date = Tools::createDateTime($date)->format('U');
		return $this->filter(function($file) use ($operator, $date) {
			return Tools::compare($file->getMTime(), $operator, $date);
		});
	}

}
