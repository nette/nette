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
	Nette\Http\FileUpload;



/**
 * Text box and browse button that allow users to select a file to upload to the server.
 *
 * @author     David Grudl
 */
class UploadControl extends BaseControl
{

	/**
	 * @param  string  label
	 * @param  bool  allows to upload multiple files
	 */
	public function __construct($label = NULL, $multiple = FALSE)
	{
		parent::__construct($label);
		$this->control->type = 'file';
		$this->control->multiple = (bool) $multiple;
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof Nette\Forms\Form) {
			if ($form->getMethod() !== Nette\Forms\Form::POST) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
		parent::attached($form);
	}



	/**
	 * Sets control's value.
	 * @return UploadControl  provides a fluent interface
	 */
	public function setValue($value)
	{
		return $this;
	}



	protected function setRawValue($value)
	{
		if ($this->control->multiple) {
			$this->value = array();
			foreach (is_array($value) ? $value : array() as $item) {
				if ($item instanceof FileUpload) {
					$this->value[] = $item;
				}
			}
		} elseif (!$value instanceof FileUpload) {
			$this->value = new FileUpload(NULL);
		} else {
			$this->value = $value;
		}
		return $this;
	}



	/**
	 * Has been any file uploaded?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->value instanceof FileUpload ? $this->value->isOk() : (bool) $this->value; // ignore NULL object
	}



	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}



	/**
	 * FileSize validator: is file size in limit?
	 * @param  UploadControl
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(UploadControl $control, $limit)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if ($file->getSize() > $limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * MimeType validator: has file specified mime type?
	 * @param  UploadControl
	 * @param  array|string  mime type
	 * @return bool
	 */
	public static function validateMimeType(UploadControl $control, $mimeType)
	{
		$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
		foreach (static::toArray($control->getValue()) as $file) {
			$type = strtolower($file->getContentType());
			if (!in_array($type, $mimeTypes, TRUE) && !in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * Image validator: is file image?
	 * @return bool
	 */
	public static function validateImage(UploadControl $control)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if (!$file->isImage()) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * @return array
	 */
	public static function toArray($value)
	{
		return $value instanceof FileUpload ? array($value) : (array) $value;
	}

}
