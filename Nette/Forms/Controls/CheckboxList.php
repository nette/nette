<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
		$this->separator = Html::el('br');
	}


	/**
	 * Generates control's HTML element.
	 * @return string
	 */
	public function getControl()
	{
		$items = $this->getItems();
		reset($items);
		$input = parent::getControl();
		return Nette\Forms\Helpers::createInputList(
			$this->translate($items),
			array_merge($input->attrs, array(
				'id' => NULL,
				'checked?' => $this->value,
				'disabled:' => $this->disabled,
				'required' => NULL,
				'data-nette-rules:' => array(key($items) => $input->attrs['data-nette-rules']),
			)),
			$this->label->attrs,
			$this->separator
		);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getLabel($caption = NULL)
	{
		return parent::getLabel($caption)->for(NULL);
	}


	/**
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->separator;
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getControlPart($key)
	{
		return parent::getControl()->addAttributes(array(
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, TRUE),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'required' => NULL,
			'value' => $key,
		));
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getLabelPart($key)
	{
		return parent::getLabel($this->items[$key])->for($this->getHtmlId() . '-' . $key);
	}

}
