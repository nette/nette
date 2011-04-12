<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templates;

use Nette;



/**
 * The exception occured during template compilation.
 *
 * @author     David Grudl
 */
class TemplateException extends \InvalidStateException implements Nette\IDebugPanel
{
	/** @var string */
	public $sourceFile;

	/** @var int */
	public $sourceLine;



	function __construct($message, $code = 0, $sourceLine = 0)
	{
		$this->sourceLine = (int) $sourceLine;
		parent::__construct($message, $code);
	}



	function setSourceFile($file)
	{
		$this->sourceFile = (string) $file;
		$this->message = rtrim($this->message, '.') . " in " . str_replace(dirname(dirname($file)), '...', $file)
			. ($this->sourceLine ? ":$this->sourceLine" : '');
	}



	function getTab()
	{
		return 'Template';
	}



	function getPanel()
	{
		$link = Nette\DebugHelpers::editorLink($this->sourceFile, $this->sourceLine);
		return '<p><b>File:</b> ' . ($link ? '<a href="' . htmlspecialchars($link) . '">' : '') . htmlspecialchars($this->sourceFile) . ($link ? '</a>' : '')
			. '&nbsp; <b>Line:</b> ' . ($this->sourceLine ? $this->sourceLine : 'n/a') . '</p>'
			. ($this->sourceLine ? '<pre>' . Nette\DebugHelpers::highlightFile($this->sourceFile, $this->sourceLine) . '</pre>' : '');
	}



	function getId()
	{
	}

}
