<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Collections
 */

/*namespace Nette\Collections;*/



/**
 * Provides the base class for a generic list (items can be accessed by index).
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Collections
 */
class ArrayList extends Collection implements IList
{
	/** @var int */
	protected $base = 0;


	/**
	 * Inserts the specified element at the specified position in this list.
	 * @param  int
	 * @param  mixed
	 * @return bool
	 * @throws \ArgumentOutOfRangeException
	 */
	public function insertAt($index, $item)
	{
		$index -= $this->base;
		if ($index < 0 || $index > count($this)) {
			throw new /*\*/ArgumentOutOfRangeException;
		}

		$this->beforeAdd($item);
		$data = $this->getArrayCopy();
		array_splice($data, (int) $index, 0, array($item));
		$this->setArray($data);
		return TRUE;
	}



	/**
	 * Removes the first occurrence of the specified element.
	 * @param  mixed
	 * @return bool  true if this list changed as a result of the call
	 * @throws \NotSupportedException
	 */
	public function remove($item)
	{
		$this->updating();

		$index = $this->search($item);
		if ($index === FALSE) {
			return FALSE;
		} else {
			$data = $this->getArrayCopy();
			array_splice($data, $index, 1);
			$this->setArray($data);
			return TRUE;
		}
	}



	/**
	 * Returns the index of the first occurrence of the specified element,.
	 * or FALSE if this list does not contain this element.
	 * @param  mixed
	 * @return int|FALSE
	 */
	public function indexOf($item)
	{
		$index = $this->search($item);
		return $index === FALSE ? FALSE : $this->base + $index;
	}



	/********************* interface \ArrayAccess ****************d*g**/



	/**
	 * Replaces (or appends) the item (\ArrayAccess implementation).
	 * @param  int index
	 * @param  object
	 * @return void
	 * @throws \InvalidArgumentException, \NotSupportedException, \ArgumentOutOfRangeException
	 */
	public function offsetSet($index, $item)
	{
		$this->beforeAdd($item);

		if ($index === NULL)  { // append
			parent::offsetSet(NULL, $item);

		} else { // replace
			$index -= $this->base;
			if ($index < 0 || $index >= count($this)) {
				throw new /*\*/ArgumentOutOfRangeException;
			}
			parent::offsetSet($index, $item);
		}
	}



	/**
	 * Returns item (\ArrayAccess implementation).
	 * @param  int index
	 * @return mixed
	 * @throws \ArgumentOutOfRangeException
	 */
	public function offsetGet($index)
	{
		$index -= $this->base;
		if ($index < 0 || $index >= count($this)) {
			throw new /*\*/ArgumentOutOfRangeException;
		}

		return parent::offsetGet($index);
	}



	/**
	 * Exists item? (\ArrayAccess implementation).
	 * @param  int index
	 * @return bool
	 */
	public function offsetExists($index)
	{
		$index -= $this->base;
		return $index >= 0 && $index < count($this);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * @param  int index
	 * @return void
	 * @throws \NotSupportedException, \ArgumentOutOfRangeException
	 */
	public function offsetUnset($index)
	{
		$this->updating();

		$index -= $this->base;
		if ($index < 0 || $index >= count($this)) {
			throw new /*\*/ArgumentOutOfRangeException;
		}

		$data = $this->getArrayCopy();
		array_splice($data, (int) $index, 1);
		$this->setArray($data);
	}

}