<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * @author Jan Skrasek
 */
class PersistentField extends HiddenField
{
	/** @var mixed */
	protected $persistentValue;


	public function __construct($value)
	{
		parent::__construct();
		$this->persistentValue = $value;
		$this->setValue($value);
		$this->setOmitted();
		$this->setHtmlId(FALSE);
		$this->unmonitor('Nette\Forms\Form');
	}


	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->value($this->persistentValue);
	}

}
