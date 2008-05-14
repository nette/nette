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



require_once dirname(__FILE__) . '/../Collections/Collection.php';

require_once dirname(__FILE__) . '/../Collections/ISet.php';



/**
 * Provides the base class for a collection that contains no duplicate elements.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Collections
 * @version    $Revision$ $Date$
 */
class Set extends Collection implements ISet
{


	/**
	 * Appends the specified element to the end of this collection.
	 * @param  mixed
	 * @return bool  true if this collection changed as a result of the call
	 * @throws ::InvalidArgumentException, ::NotSupportedException
	 */
	public function append($item)
	{
		$this->beforeAdd($item);

		if (is_object($item)) {
			$key = spl_object_hash($item);
			if (parent::offsetExists($key)) {
				return FALSE;
			}
			parent::offsetSet($key, $item);
			return TRUE;

		} else {
			$key = $this->search($item);
			if ($key === FALSE) {
				parent::offsetSet(NULL, $item);
				return TRUE;
			}
			return FALSE;
		}
	}



	/**
	 * Returns the index of the first occurrence of the specified element,
	 * or FALSE if this collection does not contain this element.
	 * @param  mixed
	 * @return int|FALSE
	 * @throws ::InvalidArgumentException
	 */
	protected function search($item)
	{
		if (is_object($item)) {
			$key = spl_object_hash($item);
			return parent::offsetExists($key) ? $key : FALSE;

		} else {
			return array_search($item, $this->getArrayCopy(), TRUE);
		}
	}



	/********************* ArrayObject cooperation ****************d*g**/



	/**
	 * Not supported (only appending).
	 */
	public function offsetSet($key, $item)
	{
		if ($key === NULL) {
			$this->append($item);
		} else {
			throw new /*::*/NotSupportedException;
		}
	}



	/**
	 * Not supported.
	 */
	public function offsetGet($key)
	{
		throw new /*::*/NotSupportedException;
	}



	/**
	 * Not supported.
	 */
	public function offsetExists($key)
	{
		throw new /*::*/NotSupportedException;
	}



	/**
	 * Not supported.
	 */
	public function offsetUnset($key)
	{
		throw new /*::*/NotSupportedException;
	}

}