<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Html,
	Nette\Localization\ITranslator;


/**
 * Forms helpers.
 *
 * @author     David Grudl
 */
class Helpers extends Nette\Object
{

	/**
	 * Extracts and sanitizes submitted form data for single control.
	 * @param  array   submitted data
	 * @param  string  control HTML name
	 * @param  string  type Form::DATA_TEXT, DATA_LINE, DATA_FILE
	 * @return string|string[]
	 */
	public static function extractHttpData(array $data, $htmlName, $type)
	{
		$name = explode('[', str_replace(array('[]', ']', '.'), array('', '', '_'), $htmlName));
		$data = Nette\Utils\Arrays::get($data, $name, NULL);

		if (substr($htmlName, -2) === '[]') {
			$arr = array();
			foreach (is_array($data) ? $data : array() as $v) {
				$arr[] = $v = static::sanitize($type, $v);
				if ($v === NULL) {
					return array();
				}
			}
			return $arr;
		} else {
			return static::sanitize($type, $data);
		}
	}


	private static function sanitize($type, $value)
	{
		if ($type === Form::DATA_TEXT) {
			return is_scalar($value) ? Strings::normalizeNewLines($value) : NULL;

		} elseif ($type === Form::DATA_LINE) {
			return is_scalar($value) ? Strings::trim(strtr($value, "\r\n", '  ')) : NULL;

		} elseif ($type === Form::DATA_FILE) {
			return $value instanceof Nette\Http\FileUpload ? $value : NULL;

		} else {
			throw new Nette\InvalidArgumentException('Unknown data type');
		}
	}


	/**
	 * Converts control name to HTML name.
	 * @return string
	 */
	public static function generateHtmlName($id)
	{
		$name = str_replace(Nette\ComponentModel\IComponent::NAME_SEPARATOR, '][', $id, $count);
		if ($count) {
			$name = substr_replace($name, '', strpos($name, ']'), 1) . ']';
		}
		if (is_numeric($name) || in_array($name, array('attributes','children','elements','focus','length','reset','style','submit','onsubmit'))) {
			$name = '_' . $name;
		}
		return $name;
	}


	/**
	 * @return array
	 */
	public static function exportRules(Rules $rules, $json = TRUE)
	{
		$payload = array();
		foreach ($rules as $rule) {
			if (!is_string($op = $rule->operation)) {
				if (!Nette\Utils\Callback::isStatic($op)) {
					continue;
				}
				$op = Nette\Utils\Callback::toString($op);
			}
			if ($rule->type === Rule::VALIDATOR) {
				$item = array('op' => ($rule->isNegative ? '~' : '') . $op, 'msg' => Validator::formatMessage($rule, FALSE));

			} elseif ($rule->type === Rule::CONDITION) {
				$item = array(
					'op' => ($rule->isNegative ? '~' : '') . $op,
					'rules' => static::exportRules($rule->subRules, FALSE),
					'control' => $rule->control->getHtmlName()
				);
				if ($rule->subRules->getToggles()) {
					$item['toggle'] = $rule->subRules->getToggles();
				}
			}

			if (is_array($rule->arg)) {
				foreach ($rule->arg as $key => $value) {
					$item['arg'][$key] = $value instanceof IControl ? array('control' => $value->getHtmlName()) : $value;
				}
			} elseif ($rule->arg !== NULL) {
				$item['arg'] = $rule->arg instanceof IControl ? array('control' => $rule->arg->getHtmlName()) : $rule->arg;
			}

			$payload[] = $item;
		}
		return $json
			? ($payload ? Nette\Utils\Json::encode($payload) : NULL)
			: $payload;
	}


	/**
	 * @return string
	 */
	public static function createInputList(array $items, array $inputAttrs = NULL, array $labelAttrs = NULL, ITranslator $translator = NULL, $separator = NULL)
	{
		list($inputAttrs, $inputTag) = self::prepareAttrs($inputAttrs, 'input');
		list($labelAttrs, $labelTag) = self::prepareAttrs($labelAttrs, 'label');
		$res = '';
		$input = Html::el();
		$label = Html::el();
		foreach ($items as $value => $caption) {
			foreach ($inputAttrs as $k => $v) {
				$input->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
			}
			foreach ($labelAttrs as $k => $v) {
				$label->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
			}
			$input->value = $value;
			$res .= $labelTag . $label->attributes() . '>'
				. $inputTag . $input->attributes() . (Html::$xhtml ? ' />' : '>')
				. ($caption instanceof Html ? $caption : htmlspecialchars($translator ? $translator->translate($caption) : $caption))
				. '</label>'
				. $separator;
		}
		return $res;
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public static function createSelectBox(array $items, array $optionAttrs = NULL, ITranslator $translator = NULL)
	{
		list($optionAttrs, $optionTag) = self::prepareAttrs($optionAttrs, 'option');
		$option = Html::el();
		$res = $tmp = '';
		foreach ($items as $group => $subitems) {
			if (is_array($subitems)) {
				$res .= Html::el('optgroup')->label($translator ? $translator->translate($group) : $group)->startTag();
				$tmp = '</optgroup>';
			} else {
				$subitems = array($group => $subitems);
			}
			foreach ($subitems as $value => $caption) {
				$option->value = $value;
				foreach ($optionAttrs as $k => $v) {
					$option->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
				}
				if ($caption instanceof Html) {
					$caption = clone $caption;
					$res .= $caption->setName('option')->addAttributes($option->attrs);
				} else {
					$res .= $optionTag . $option->attributes() . '>'
						. htmlspecialchars($translator ? $translator->translate($caption) : $caption)
						. '</option>';
				}
			}
			$res .= $tmp;
			$tmp = '';
		}
		return Html::el('select')->setHtml($res);
	}


	private static function prepareAttrs($attrs, $name)
	{
		$dynamic = array();
		foreach ((array) $attrs as $k => $v) {
			$parts = explode('|', $k);
			if (!isset($parts[1])) {
				continue;
			}
			unset($attrs[$k], $attrs[$parts[0]]);
			if ($parts[1] === '?') {
				$dynamic[$parts[0]] = array_fill_keys((array) $v, TRUE);
			} elseif (is_array($v)) { // *
				$dynamic[$parts[0]] = $v;
			} else {
				$attrs[$parts[0]] = $v;
			}
		}
		return array($dynamic, '<' . $name . Html::el(NULL, $attrs)->attributes());
	}

}
