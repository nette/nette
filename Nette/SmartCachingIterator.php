<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



/**
 * Smarter caching interator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
class SmartCachingIterator extends /*\*/CachingIterator
{
	/** @var int */
	private $counter;



	public function __construct($iterator)
	{
		if (is_array($iterator)) {
			parent::__construct(new /*\*/ArrayIterator($iterator), 0);

		} elseif ($iterator instanceof /*\*/IteratorAggregate) {
			parent::__construct($iterator->getIterator(), 0);

		} else {
			parent::__construct($iterator, 0);
		}
	}



	/**
	 * Is the current element the first one?
	 * @return bool
	 */
	public function isFirst()
	{
		return $this->counter === 0;
	}



	/**
	 * Is the current element the last one?
	 * @return bool
	 */
	public function isLast()
	{
		return !$this->hasNext();
	}



	/**
	 * Is the iterator empty?
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->counter === NULL;
	}



	/**
	 * Is the counter odd?
	 * @return bool
	 */
	public function isOdd()
	{
		return $this->counter % 2 === 1;
	}



	/**
	 * Is the counter even?
	 * @return bool
	 */
	public function isEven()
	{
		return $this->counter % 2 === 0;
	}



	/**
	 * Returns the counter.
	 * @return int
	 */
	public function getCounter()
	{
		return $this->counter;
	}



	/**
	 * Forwards to the next element.
	 * @return void
	 */
	public function next()
	{
		parent::next();
		if (parent::valid()) {
			$this->counter++;
		}
	}



	/**
	 * Rewinds the Iterator.
	 * @return void
	 */
	public function rewind()
	{
		parent::rewind();
		$this->counter = 0;
	}

}
