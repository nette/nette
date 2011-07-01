<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Diagnostics;

use Nette,
	Nette\Database\Connection,
	Nette\Diagnostics\Debugger;



/**
 * Debug panel for Nette\Database.
 *
 * @author     David Grudl
 */
class ConnectionPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	/** @var int maximum SQL length */
	static public $maxLength = 1000;

	/** @var int logged time */
	public $totalTime = 0;

	/** @var array */
	public $queries = array();

	/** @var string */
	public $name;

	/** @var bool explain queries? */
	public $explain = TRUE;

	/** @var bool */
	public $disabled = FALSE;



	public static function initialize(Connection $connection)
	{
		$panel = new static;
		Debugger::$blueScreen->addPanel(array($panel, 'renderException'), __CLASS__);
		if (!Debugger::$productionMode) {
			$connection->onQuery[] = callback($panel, 'logQuery');
			Debugger::$bar->addPanel($panel);
		}
	}



	public function logQuery(Nette\Database\Statement $result, array $params = NULL)
	{
		if ($this->disabled) {
			return;
		}
		$source = NULL;
		foreach (debug_backtrace(FALSE) as $row) {
			if (isset($row['file']) && is_file($row['file']) && strpos($row['file'], NETTE_DIR . DIRECTORY_SEPARATOR) !== 0) {
				$source = array($row['file'], (int) $row['line']);
				break;
			}
		}
		$this->totalTime += $result->time;
		$this->queries[] = array($result->queryString, $params, $result->time, $result->rowCount(), $result->getConnection(), $source);
	}



	public function renderException($e)
	{
		if ($e instanceof \PDOException && isset($e->queryString)) {
			return array(
				'tab' => 'SQL',
				'panel' => Connection::highlightSql($e->queryString),
			);
		}
	}



	public function getTab()
	{
		return '<span title="Nette\\Database ' . htmlSpecialChars($this->name) . '">'
			. '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC" />'
			. count($this->queries) . ' queries'
			. ($this->totalTime ? ' / ' . sprintf('%0.1f', $this->totalTime * 1000) . 'ms' : '')
			. '</span>';
	}



	public function getPanel()
	{
		$this->disabled = TRUE;
		$s = '';
		$h = 'htmlSpecialChars';
		foreach ($this->queries as $i => $query) {
			list($sql, $params, $time, $rows, $connection, $source) = $query;

			$explain = NULL; // EXPLAIN is called here to work SELECT FOUND_ROWS()
			if ($this->explain && preg_match('#\s*SELECT\s#iA', $sql)) {
				try {
					$explain = $connection->queryArgs('EXPLAIN ' . $sql, $params)->fetchAll();
				} catch (\PDOException $e) {}
			}

			$s .= '<tr><td>' . sprintf('%0.3f', $time * 1000);
			if ($explain) {
				static $counter;
				$counter++;
				$s .= "<br /><a href='#' class='nette-toggler' rel='#nette-DbConnectionPanel-row-$counter'>explain&nbsp;&#x25ba;</a>";
			}

			$s .= '</td><td class="nette-DbConnectionPanel-sql">' . Connection::highlightSql(Nette\Utils\Strings::truncate($sql, self::$maxLength));
			if ($explain) {
				$s .= "<table id='nette-DbConnectionPanel-row-$counter' class='nette-collapsed'><tr>";
				foreach ($explain[0] as $col => $foo) {
					$s .= "<th>{$h($col)}</th>";
				}
				$s .= "</tr>";
				foreach ($explain as $row) {
					$s .= "<tr>";
					foreach ($row as $col) {
						$s .= "<td>{$h($col)}</td>";
					}
					$s .= "</tr>";
				}
				$s .= "</table>";
			}
			if ($source) {
				$s .= Nette\Diagnostics\Helpers::editorLink($source[0], $source[1])->class('nette-DbConnectionPanel-source');
			}

			$s .= '</td><td>';
			foreach ($params as $param) {
				$s .= Debugger::dump($param, TRUE);
			}

			$s .= '</td><td>' . $rows . '</td></tr>';
		}

		return empty($this->queries) ? '' :
			'<style> #nette-debug td.nette-DbConnectionPanel-sql { background: white !important }
			#nette-debug .nette-DbConnectionPanel-source { color: #BBB !important }
			#nette-debug nette-DbConnectionPanel tr table { margin: 8px 0; max-height: 150px; overflow:auto } </style>
			<h1>Queries: ' . count($this->queries) . ($this->totalTime ? ', time: ' . sprintf('%0.3f', $this->totalTime * 1000) . ' ms' : '') . '</h1>
			<div class="nette-inner nette-DbConnectionPanel">
			<table>
				<tr><th>Time&nbsp;ms</th><th>SQL Statement</th><th>Params</th><th>Rows</th></tr>' . $s . '
			</table>
			</div>';
	}

}
