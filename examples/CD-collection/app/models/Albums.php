<?php

use Nette\Object,
	Nette\Environment;


/**
 * Albums model.
 */
class Albums extends Object
{
	/** @var string */
	private $table = 'albums';

	/** @var DibiConnection */
	private $connection;


	public static function initialize()
	{
		dibi::connect(Environment::getConfig('database'));
	}



	public function __construct()
	{
		$this->connection = dibi::getConnection();
	}



	public function findAll()
	{
		return $this->connection->select('*')->from($this->table);
	}



	public function find($id)
	{
		return $this->connection->select('*')->from($this->table)->where('id=%i', $id);
	}



	public function update($id, array $data)
	{
		return $this->connection->update($this->table, $data)->where('id=%i', $id)->execute();
	}



	public function insert(array $data)
	{
		return $this->connection->insert($this->table, $data)->execute(dibi::IDENTIFIER);
	}



	public function delete($id)
	{
		return $this->connection->delete($this->table)->where('id=%i', $id)->execute();
	}

}