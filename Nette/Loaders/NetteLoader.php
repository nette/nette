<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Loaders;

use Nette;



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 */
class NetteLoader extends AutoLoader
{
	/** @var NetteLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'ArgumentOutOfRangeException' => '/Utils/exceptions',
		'DeprecatedException' => '/Utils/exceptions',
		'DirectoryNotFoundException' => '/Utils/exceptions',
		'FatalErrorException' => '/Utils/exceptions',
		'FileNotFoundException' => '/Utils/exceptions',
		'IOException' => '/Utils/exceptions',
		'InvalidStateException' => '/Utils/exceptions',
		'MemberAccessException' => '/Utils/exceptions',
		'Nette\AmbiguousServiceException' => '/Environment/ServiceLocator',
		'Nette\Annotations' => '/Reflection/Annotations',
		'Nette\Application\AbortException' => '/Application/Exceptions/AbortException',
		'Nette\Application\ApplicationException' => '/Application/Exceptions/ApplicationException',
		'Nette\Application\BadRequestException' => '/Application/Exceptions/BadRequestException',
		'Nette\Application\BadSignalException' => '/Application/Exceptions/BadSignalException',
		'Nette\Application\CliRouter' => '/Application/Routers/CliRouter',
		'Nette\Application\DownloadResponse' => '/Application/Responses/DownloadResponse',
		'Nette\Application\ForbiddenRequestException' => '/Application/Exceptions/ForbiddenRequestException',
		'Nette\Application\ForwardingResponse' => '/Application/Responses/ForwardingResponse',
		'Nette\Application\IPartiallyRenderable' => '/Application/IRenderable',
		'Nette\Application\InvalidLinkException' => '/Application/Exceptions/InvalidLinkException',
		'Nette\Application\InvalidPresenterException' => '/Application/Exceptions/InvalidPresenterException',
		'Nette\Application\JsonResponse' => '/Application/Responses/JsonResponse',
		'Nette\Application\MultiRouter' => '/Application/Routers/MultiRouter',
		'Nette\Application\RedirectingResponse' => '/Application/Responses/RedirectingResponse',
		'Nette\Application\RenderResponse' => '/Application/Responses/RenderResponse',
		'Nette\Application\Route' => '/Application/Routers/Route',
		'Nette\Application\SimpleRouter' => '/Application/Routers/SimpleRouter',
		'Nette\ArrayTools' => '/Utils/ArrayTools',
		'Nette\Callback' => '/Utils/Callback',
		'Nette\Collections\KeyNotFoundException' => '/Collections/Hashtable',
		'Nette\Component' => '/ComponentModel/Component',
		'Nette\ComponentContainer' => '/ComponentModel/ComponentContainer',
		'Nette\Configurator' => '/Environment/Configurator',
		'Nette\DI\Diagnostics\ContainerPanel' => '/DI/Diagnostics/ContainerPanel.php.LOCAL',
		'Nette\DateTime' => '/Utils/DateTime',
		'Nette\Debug' => '/Debug/Debug',
		'Nette\Environment' => '/Environment/Environment',
		'Nette\Forms\Button' => '/Forms/Controls/Button',
		'Nette\Forms\Checkbox' => '/Forms/Controls/Checkbox',
		'Nette\Forms\ConventionalRenderer' => '/Forms/Renderers/ConventionalRenderer',
		'Nette\Forms\FileUpload' => '/Forms/Controls/FileUpload',
		'Nette\Forms\FormControl' => '/Forms/Controls/FormControl',
		'Nette\Forms\HiddenField' => '/Forms/Controls/HiddenField',
		'Nette\Forms\ImageButton' => '/Forms/Controls/ImageButton',
		'Nette\Forms\InstantClientScript' => '/Forms/Renderers/InstantClientScript',
		'Nette\Forms\MultiSelectBox' => '/Forms/Controls/MultiSelectBox',
		'Nette\Forms\RadioList' => '/Forms/Controls/RadioList',
		'Nette\Forms\SelectBox' => '/Forms/Controls/SelectBox',
		'Nette\Forms\SubmitButton' => '/Forms/Controls/SubmitButton',
		'Nette\Forms\TextArea' => '/Forms/Controls/TextArea',
		'Nette\Forms\TextBase' => '/Forms/Controls/TextBase',
		'Nette\Forms\TextInput' => '/Forms/Controls/TextInput',
		'Nette\Framework' => '/Utils/Framework',
		'Nette\FreezableObject' => '/Utils/FreezableObject',
		'Nette\GenericRecursiveIterator' => '/Utils/Iterators/GenericRecursiveIterator',
		'Nette\IComponent' => '/ComponentModel/IComponent',
		'Nette\IComponentContainer' => '/ComponentModel/IComponentContainer',
		'Nette\IDebuggable' => '/Debug/IDebuggable',
		'Nette\IO\SafeStream' => '/Utils/SafeStream',
		'Nette\IServiceLocator' => '/Environment/IServiceLocator',
		'Nette\ITranslator' => '/Utils/ITranslator',
		'Nette\Image' => '/Utils/Image',
		'Nette\ImageMagick' => '/Utils/ImageMagick',
		'Nette\InstanceFilterIterator' => '/Utils/Iterators/InstanceFilterIterator',
		'Nette\Object' => '/Utils/Object',
		'Nette\ObjectMixin' => '/Utils/ObjectMixin',
		'Nette\Paginator' => '/Utils/Paginator',
		'Nette\RecursiveComponentIterator' => '/ComponentModel/ComponentContainer',
		'Nette\ServiceLocator' => '/Environment/ServiceLocator',
		'Nette\SmartCachingIterator' => '/Utils/Iterators/SmartCachingIterator',
		'Nette\String' => '/Utils/String',
		'Nette\Templates\CachingHelper' => '/Templates/Filters/CachingHelper',
		'Nette\Templates\CurlyBracketsFilter' => '/Templates/Filters/LatteFilter',
		'Nette\Templates\CurlyBracketsMacros' => '/Templates/Filters/LatteFilter',
		'Nette\Templates\LatteFilter' => '/Templates/Filters/LatteFilter',
		'Nette\Templates\LatteMacros' => '/Templates/Filters/LatteMacros',
		'Nette\Templates\SnippetHelper' => '/Templates/Filters/SnippetHelper',
		'Nette\Templates\TemplateFilters' => '/Templates/Filters/TemplateFilters',
		'Nette\Templates\TemplateHelpers' => '/Templates/Filters/TemplateHelpers',
		'Nette\Tools' => '/Utils/Tools',
		'Nette\Web\FtpException' => '/Web/Ftp',
		'NotImplementedException' => '/Utils/exceptions',
		'NotSupportedException' => '/Utils/exceptions',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim($type, '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(NETTE_DIR . $this->list[$type] . '.php', TRUE);
			self::$count++;

		} elseif (substr($type, 0, 6) === 'Nette\\' && is_file($file = NETTE_DIR . strtr(substr($type, 5), '\\', '/') . '.php')) {
			LimitedScope::load($file, TRUE);
			self::$count++;
		}
	}

}
