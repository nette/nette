<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Collections
 */

/*namespace Nette::Collections;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Collections/ICollection.php';



/**
 * Provides the base class for a generic collection.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Collections
 * @version    $Revision$ $Date$
 */
class Collection extends /*Nette::*/Object implements ICollection
{
	/** @var array  of objects */
	protected $data = array();

	/** @var string  type (class, interface, PHP type) */
	protected $itemType;

	/** @var string  function to verify type */
	protected $checkFunc;

	/** @var bool */
	protected $readOnly = FALSE;



	/**
	 * @param  array to wrap
	 * @param  string class/interface name or ':type'
	 * @throws ::InvalidArgumentException
	 */
	public function __construct($arr = NULL, $type = NULL)
	{
		if (substr($type, 0, 1) === ':') {
			$this->itemType = substr($type, 1);
			$this->checkFunc = 'is_' . $this->itemType;
		} else {
			$this->itemType = $type;
		}

		if ($arr !== NULL) {
			$this->import($arr);
		}
	}



	/**
	 * Prevent any more modifications.
	 * @return void
	 */
	public function setReadOnly()
	{
		$this->readOnly = TRUE;
	}



	/**
	 * Appends the specified element to the end of this collection.
	 * @param  mixed
	 * @return bool  true if this collection changed as a result of the call
	 * @throws ::InvalidArgumentException, ::NotSupportedException
	 */
	public function add($item)
	{
		$this->beforeAdd($item);
		$this->data[] = $item;
		return TRUE;
	}



	/**
	 * Removes the first occurrence of the specified element.
	 * @param  mixed
	 * @return bool  true if this collection changed as a result of the call
	 * @throws ::NotSupportedException
	 */
	public function remove($item)
	{
		$this->beforeRemove();
		$index = $this->search($item);
		if ($index === FALSE) {
			return FALSE;
		} else {
			unset($this->data[$index]);
			return TRUE;
		}
	}



	/**
	 * Returns the index of the first occurrence of the specified element,.
	 * or FALSE if this collection does not contain this element.
	 * @param  mixed
	 * @return int|FALSE
	 */
	protected function search($item)
	{
		return array_search($item, $this->data, TRUE);
	}



	/**
	 * Removes all of the elements from this collection.
	 * @return void
	 * @throws ::NotSupportedException
	 */
	public function clear()
	{
		$this->beforeRemove();
		$this->data = array();
	}



	/**
	 * Returns true if this collection contains the specified item.
	 * @param  mixed
	 * @return bool
	 */
	public function contains($item)
	{
		return $this->search($item) !== FALSE;
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|Traversable
	 * @return void
	 * @throws ::InvalidArgumentException
	 */
	public function import($arr)
	{
		if (is_array($arr) || $arr instanceof Traversable) {
			foreach ($arr as $item) {
				$this->add($item);
			}
		} else {
			throw new /*::*/InvalidArgumentException("Argument must be traversable.");
		}
	}



	/**
	 * Returns an array containing all of the elements in this collection.
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}



	/**
	 * Returns a value indicating whether collection is read-only.
	 * @return bool
	 */
	public function isReadOnly()
	{
		return $this->readOnly;
	}



	/**
	 * Returns the number of elements in collection (::Countable implementation).
	 * @return int
	 */
	public function count()
	{
		return count($this->data);
	}



	/**
	 * Returns an iterator over the elements in collection (::IteratorAggregate implementation).
	 * @return ::ArrayIterator
	 */
	public function getIterator()
	{
		return new /*::*/ArrayIterator($this->data);
	}



	/********************* internal notifications ****************d*g**/



	/**
	 * Responds when the item is about to be added to the collection.
	 * @param  mixed
	 * @return void
	 * @throws ::InvalidArgumentException, ::NotSupportedException
	 */
	protected function beforeAdd($item)
	{
		if ($this->readOnly) {
			throw new /*::*/NotSupportedException('Collection is read-only.');
		}

		if ($this->itemType !== NULL) {
			if ($this->checkFunc === NULL) {
				if (!($item instanceof $this->itemType)) {
					throw new /*::*/InvalidArgumentException("Item must be '$this->itemType' object.");
				}
			} else {
				$fnc = $this->checkFunc;
				if (!$fnc($item)) {
					throw new /*::*/InvalidArgumentException("Item must be $this->itemType.");
				}
			}
		}
	}



	/**
	 * Responds when an item is about to be removed from the collection.
	 * @return void
	 * @throws ::NotSupportedException
	 */
	protected function beforeRemove()
	{
		if ($this->readOnly) {
			throw new /*::*/NotSupportedException('Collection is read-only.');
		}
	}

}