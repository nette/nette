<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms;

use Nette,
	Nette\Web\HttpUploadedFile;



/**
 * Text box and browse button that allow users to select a file to upload to the server.
 *
 * @author     David Grudl
 */
class FileUpload extends FormControl
{

	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL, $multiple = FALSE)
	{
		parent::__construct($label);
		$this->control->multiple = (bool) $multiple;
		$this->control->type = 'file';
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof Form) {
			if ($form->getMethod() !== Form::POST) {
				throw new \InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
		parent::attached($form);
	}



	/**
	 * Sets control's value.
	 * @param  array|Nette\Web\HttpUploadedFile
	 * @return FileUpload  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			if (isset($value[0])) {
				$this->value = $value;
			} else {
				$this->value = new HttpUploadedFile($value);
			}
		} elseif ($value instanceof HttpUploadedFile) {
			$this->value = $value;

		} else {
			$this->value = new HttpUploadedFile(NULL);
		}
		return $this;
	}



	/**
	 * Has been any file uploaded?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->value instanceof HttpUploadedFile && $this->value->isOK();
	}



	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName(){
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}



	/**
	 * FileSize validator: is file size in limit?
	 * @param  FileUpload
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(FileUpload $control, $limit)
	{
		$file = $control->getValue();
		return $file instanceof HttpUploadedFile && $file->getSize() <= $limit;
	}



	/**
	 * MimeType validator: has file specified mime type?
	 * @param  FileUpload
	 * @param  array|string  mime type
	 * @return bool
	 */
	public static function validateMimeType(FileUpload $control, $mimeType)
	{
		$file = $control->getValue();
		if ($file instanceof HttpUploadedFile) {
			$type = strtolower($file->getContentType());
			$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
			if (in_array($type, $mimeTypes, TRUE)) {
				return TRUE;
			}
			if (in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
				return TRUE;
			}
		}
		return FALSE;
	}



	/**
	 * Image validator: is file image?
	 * @param  FileUpload
	 * @return bool
	 */
	public static function validateImage(FileUpload $control)
	{
		$file = $control->getValue();
		return $file instanceof HttpUploadedFile && $file->isImage();
	}

}
