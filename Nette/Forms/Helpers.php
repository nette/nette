<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Html;


/**
 * Forms helpers.
 *
 * @author     David Grudl
 */
class Helpers extends Nette\Object
{
	private static $unsafeNames = array(
		'attributes', 'children', 'elements', 'focus', 'length', 'reset', 'style', 'submit', 'onsubmit', 'form',
		'presenter', 'action',
	);


	/**
	 * Extracts and sanitizes submitted form data for single control.
	 * @param  array   submitted data
	 * @param  string  control HTML name
	 * @param  string  type Form::DATA_TEXT, DATA_LINE, DATA_FILE, DATA_KEYS
	 * @return string|string[]
	 */
	public static function extractHttpData(array $data, $htmlName, $type)
	{
		$name = explode('[', str_replace(array('[]', ']', '.'), array('', '', '_'), $htmlName));
		$data = Nette\Utils\Arrays::get($data, $name, NULL);
		$itype = $type & ~Form::DATA_KEYS;

		if (substr($htmlName, -2) === '[]') {
			if (!is_array($data)) {
				return array();
			}
			foreach ($data as $k => $v) {
				$data[$k] = $v = static::sanitize($itype, $v);
				if ($v === NULL) {
					return array();
				}
			}
			if ($type & Form::DATA_KEYS) {
				return $data;
			}
			return array_values($data);
		} else {
			return static::sanitize($itype, $data);
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
		if (is_numeric($name) || in_array($name, self::$unsafeNames, TRUE)) {
			$name = '_' . $name;
		}
		return $name;
	}


	/**
	 * @return string
	 */
	public static function createInputList(array $items, array $inputAttrs = NULL, array $labelAttrs = NULL, $wrapper = NULL)
	{
		list($inputAttrs, $inputTag) = self::prepareAttrs($inputAttrs, 'input');
		list($labelAttrs, $labelTag) = self::prepareAttrs($labelAttrs, 'label');
		$res = '';
		$input = Html::el();
		$label = Html::el();
		list($wrapper, $wrapperEnd) = $wrapper instanceof Html ? array($wrapper->startTag(), $wrapper->endTag()) : array((string) $wrapper, '');

		foreach ($items as $value => $caption) {
			foreach ($inputAttrs as $k => $v) {
				$input->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
			}
			foreach ($labelAttrs as $k => $v) {
				$label->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
			}
			$input->value = $value;
			$res .= ($res === '' && $wrapperEnd === '' ? '' : $wrapper)
				. $labelTag . $label->attributes() . '>'
				. $inputTag . $input->attributes() . (Html::$xhtml ? ' />' : '>')
				. ($caption instanceof Html ? $caption : htmlspecialchars($caption))
				. '</label>'
				. $wrapperEnd;
		}
		return $res;
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public static function createSelectBox(array $items, array $optionAttrs = NULL)
	{
		list($optionAttrs, $optionTag) = self::prepareAttrs($optionAttrs, 'option');
		$option = Html::el();
		$res = $tmp = '';
		foreach ($items as $group => $subitems) {
			if (is_array($subitems)) {
				$res .= Html::el('optgroup')->label($group)->startTag();
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
						. htmlspecialchars($caption)
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
			$p = str_split($k, strlen($k) - 1);
			if ($p[1] === '?' || $p[1] === ':') {
				unset($attrs[$k], $attrs[$p[0]]);
				if ($p[1] === '?') {
					$dynamic[$p[0]] = array_fill_keys((array) $v, TRUE);
				} elseif (is_array($v) && $v) {
					$dynamic[$p[0]] = $v;
				} else {
					$attrs[$p[0]] = $v;
				}
			}
		}
		return array($dynamic, '<' . $name . Html::el(NULL, $attrs)->attributes());
	}

}
