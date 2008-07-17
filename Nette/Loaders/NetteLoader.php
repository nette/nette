<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Loaders
 * @version    $Id$
 */

/*namespace Nette::Loaders;*/


require_once dirname(__FILE__) . '/../Loaders/AutoLoader.php';



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Loaders
 */
class NetteLoader extends AutoLoader
{
	public $base;

	public $list = array(
		'abortexception' => '/Application/AbortException.php',
		'abstractform' => '/Forms/AbstractForm.php',
		'ajaxdriver' => '/Application/AjaxDriver.php',
		'ambiguousserviceexception' => '/ServiceLocator.php',
		'appform' => '/Forms/AppForm.php',
		'application' => '/Application/Application.php',
		'applicationexception' => '/Application/ApplicationException.php',
		'argumentoutofrangeexception' => '/exceptions.php',
		'arraylist' => '/Collections/ArrayList.php',
		'authenticationexception' => '/Security/AuthenticationException.php',
		'autoloader' => '/Loaders/AutoLoader.php',
		'badrequestexception' => '/Application/BadRequestException.php',
		'badsignalexception' => '/Application/BadSignalException.php',
		'button' => '/Forms/items.php',
		'cache' => '/Caching/Cache.php',
		'callback' => '/Callback.php',
		'checkbox' => '/Forms/items.php',
		'collection' => '/Collections/Collection.php',
		'component' => '/Component.php',
		'componentcontainer' => '/ComponentContainer.php',
		'config' => '/Config.php',
		'configadapter_ini' => '/ConfigAdapters.php',
		'configadapter_xml' => '/ConfigAdapters.php',
		'configurator' => '/Configurator.php',
		'control' => '/Application/Control.php',
		'datagrid' => '/Application/UI/DataGrid.php',
		'datagridcolumn' => '/Application/UI/DataGridColumn.php',
		'debug' => '/Debug.php',
		'dibistorage' => '/Caching/DibiStorage.php',
		'directorynotfoundexception' => '/exceptions.php',
		'dummystorage' => '/Caching/DummyStorage.php',
		'environment' => '/Environment.php',
		'fatalerrorexception' => '/exceptions.php',
		'file' => '/Forms/items.php',
		'filenotfoundexception' => '/exceptions.php',
		'filestorage' => '/Caching/FileStorage.php',
		'form' => '/Forms/Form.php',
		'formcontrol' => '/Forms/items.php',
		'forms' => '/Forms/items.php',
		'forwardingexception' => '/Application/ForwardingException.php',
		'framework' => '/Framework.php',
		'hashtable' => '/Collections/Hashtable.php',
		'hidden' => '/Forms/items.php',
		'html' => '/Web/Html.php',
		'httprequest' => '/Web/HttpRequest.php',
		'httpresponse' => '/Web/HttpResponse.php',
		'httpuploadedfile' => '/Web/HttpUploadedFile.php',
		'iajaxdriver' => '/Application/IAjaxDriver.php',
		'iauthenticator' => '/Security/IAuthenticator.php',
		'iauthorizator' => '/Security/IAuthorizator.php',
		'icachestorage' => '/Caching/ICacheStorage.php',
		'icausedexception' => '/ICausedException.php',
		'icollection' => '/Collections/ICollection.php',
		'icomponent' => '/IComponent.php',
		'icomponentcontainer' => '/IComponentContainer.php',
		'idebuggable' => '/IDebuggable.php',
		'identity' => '/Security/Identity.php',
		'iformcontrol' => '/Forms/IFormControl.php',
		'ihttprequest' => '/Web/IHttpRequest.php',
		'ihttpresponse' => '/Web/IHttpResponse.php',
		'iidentity' => '/Security/IIdentity.php',
		'ilist' => '/Collections/IList.php',
		'image' => '/Forms/items.php',
		'imap' => '/Collections/IMap.php',
		'invalidlinkexception' => '/Application/InvalidLinkException.php',
		'invalidpresenterexception' => '/Application/InvalidPresenterException.php',
		'invalidstateexception' => '/exceptions.php',
		'ioexception' => '/exceptions.php',
		'ipermissionassertion' => '/Security/Permission.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterloader' => '/Application/IPresenterLoader.php',
		'irouter' => '/Application/IRouter.php',
		'iservicelocator' => '/IServiceLocator.php',
		'iset' => '/Collections/ISet.php',
		'isignalreceiver' => '/Application/ISignalReceiver.php',
		'istatepersistent' => '/Application/IStatePersistent.php',
		'itemplate' => '/Templates/ITemplate.php',
		'keynotfoundexception' => '/Collections/Hashtable.php',
		'link' => '/Application/Link.php',
		'logger' => '/Logger.php',
		'memberaccessexception' => '/exceptions.php',
		'memcachedstorage' => '/Caching/MemcachedStorage.php',
		'multirouter' => '/Application/MultiRouter.php',
		'multiselect' => '/Forms/items.php',
		'netteloader' => '/Loaders/NetteLoader.php',
		'notimplementedexception' => '/exceptions.php',
		'notsupportedexception' => '/exceptions.php',
		'object' => '/Object.php',
		'password' => '/Forms/items.php',
		'permission' => '/Security/Permission.php',
		'presenter' => '/Application/Presenter.php',
		'presentercomponent' => '/Application/PresenterComponent.php',
		'presenterhelpers' => '/Application/PresenterHelpers.php',
		'presenterloader' => '/Application/PresenterLoader.php',
		'presenterrequest' => '/Application/PresenterRequest.php',
		'radiolist' => '/Forms/items.php',
		'repeater' => '/Forms/Repeater.php',
		'robotloader' => '/Loaders/RobotLoader.php',
		'route' => '/Application/Route.php',
		'rules' => '/Forms/Rules.php',
		'safestream' => '/IO/SafeStream.php',
		'select' => '/Forms/items.php',
		'servicelocator' => '/ServiceLocator.php',
		'session' => '/Web/Session.php',
		'sessionexception' => '/Web/Session.php',
		'sessionnamespace' => '/Web/SessionNamespace.php',
		'set' => '/Collections/Set.php',
		'simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'simpleloader' => '/Loaders/SimpleLoader.php',
		'simplerouter' => '/Application/SimpleRouter.php',
		'string' => '/String.php',
		'subform' => '/Forms/SubForm.php',
		'submitbutton' => '/Forms/items.php',
		'template' => '/Templates/Template.php',
		'templatefilters' => '/Templates/TemplateFilters.php',
		'templatestorage' => '/Templates/TemplateStorage.php',
		'text' => '/Forms/items.php',
		'textarea' => '/Forms/items.php',
		'tools' => '/Tools.php',
		'uri' => '/Web/Uri.php',
		'uriscript' => '/Web/UriScript.php',
		'user' => '/Web/User.php',
	);



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		/**/// fix for namespaced classes/interfaces in PHP < 5.3
		if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

		$type = strtolower($type);
		if (isset($this->list[$type])) {
			self::includeOnce($this->base . $this->list[$type]);
			self::$count++;
		}
	}



	/**
	 * Autoloader factory.
	 * @return NetteLoader
	 */
	public static function create()
	{
		$loader = new self;
		$loader->base = dirname(dirname(__FILE__));
		$loader->register();
		return $loader;
	}

}
