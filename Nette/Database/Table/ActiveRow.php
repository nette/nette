<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette,
	Nette\Database\Reflection\MissingReferenceException;


/**
 * Single row representation.
 * ActiveRow is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class ActiveRow extends Row
{

	/** @var bool */
	private $dataRefreshed = FALSE;

	/** @var bool */
	private $isModified = FALSE;



	/**
	 * @internal
	 * @ignore
	 */
	public function setTable($selection)
	{
		if (!$selection instanceof Selection) {
			throw new \LogicException('Row table must be instance of Nette\Database\Table\Selection.');
		}
		$this->table = $selection;
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$this->accessColumn(NULL);
		return parent::toArray();
	}


	/**
	 * Updates row.
	 * @param  array|\Traversable (column => value)
	 * @return bool
	 */
	public function update($data)
	{
		$selection = $this->table->createSelectionInstance()
			->wherePrimary($this->getPrimary());

		if ($selection->update($data)) {
			$this->isModified = TRUE;
			$selection->select('*');
			if (($row = $selection->fetch()) === FALSE) {
				throw new Nette\InvalidStateException('Database refetch failed; row does not exist!');
			}
			$this->data = $row->data;
			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * Deletes row.
	 * @return int number of affected rows
	 */
	public function delete()
	{
		$res = $this->table->createSelectionInstance()
			->wherePrimary($this->getPrimary())
			->delete();

		if ($res > 0 && ($signature = $this->getSignature(FALSE))) {
			unset($this->table[$signature]);
		}

		return $res;
	}


	/********************* interface IteratorAggregate ****************d*g**/


	public function getIterator()
	{
		$this->accessColumn(NULL);
		return parent::getIterator();
	}


	/********************* interface ArrayAccess & magic accessors ****************d*g**/



	public function __set($key, $value)
	{
		throw new Nette\DeprecatedException('ActiveRow is read-only; use update() method instead.');
	}


	public function &__get($key)
	{
		$this->accessColumn($key);
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		try {
			list($table, $column) = $this->table->getDatabaseReflection()->getBelongsToReference($this->table->getName(), $key);
			$referenced = $this->getReference($table, $column);
			if ($referenced !== FALSE) {
				$this->accessColumn($key, FALSE);
				return $referenced;
			}
		} catch(MissingReferenceException $e) {}

		$this->removeAccessColumn($key);
		throw new Nette\MemberAccessException("Cannot read an undeclared column \"$key\".");
	}


	public function __isset($key)
	{
		$this->accessColumn($key);
		if (array_key_exists($key, $this->data)) {
			return isset($this->data[$key]);
		}
		$this->removeAccessColumn($key);
		return FALSE;
	}



	protected function accessColumn($key, $selectColumn = TRUE)
	{
		$this->table->accessColumn($key, $selectColumn);
		if ($this->table->getDataRefreshed() && !$this->dataRefreshed) {
			$this->data = $this->table[$this->getSignature()]->data;
			$this->dataRefreshed = TRUE;
		}
	}


	protected function removeAccessColumn($key)
	{
		$this->table->removeAccessColumn($key);
	}


	protected function getReference($table, $column)
	{
		$this->accessColumn($column);
		return parent::getReference($table, $column);
	}

}
