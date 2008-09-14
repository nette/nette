<?php


/**
 * Albums
 *
 * @sql
 *  CREATE TABLE [albums] (
 *  [id] INTEGER  NOT NULL PRIMARY KEY,
 *  [artist] VARCHAR(100)  NOT NULL,
 *  [title] VARCHAR(100)  NOT NULL
 *  );
 */
class Albums extends DibiTable
{

	protected $blankRow = array(
		'artist' => '',
		'title' => '',
	);


}