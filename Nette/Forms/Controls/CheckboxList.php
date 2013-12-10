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

use Nette,
	Nette\Utils\Html;


/**
 * Set of checkboxes.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Utils\Html $separatorPrototype
 */
class CheckboxList extends MultiChoiceControl
{
	/** @var Nette\Utils\Html  separator element template */
	protected $separator;


	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
		$this->control->id = FALSE;
		$this->separator = Html::el('br');
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$items = array();
		foreach ($this->getItems() as $key => $label) {
			$items[$key] = $this->translate($label);
		}

		$input = parent::getControl();
		return Nette\Forms\Helpers::createInputList(
			$items,
			array_merge($input->attrs, array(
				'checked?' => $this->value,
				'disabled:' => $this->disabled,
				'data-nette-rules:' => array(key($items) => $input->attrs['data-nette-rules']),
			)),
			$this->label->attrs,
			$this->separator
		);
	}


	/**
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->separator;
	}

}
