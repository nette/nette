<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Loaders
 */

/*namespace Nette::Loaders;*/


require_once dirname(__FILE__) . '/../Loaders/AutoLoader.php';



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Loaders
 * @version    $Revision$ $Date$
 */
class NetteLoader extends AutoLoader
{
	public $base;

	public $list = array(
		'abortexception' => '/Application/AbortException.php',
		'abstractform' => '/Forms/AbstractForm.php',
		'ambiguousserviceexception' => '/ServiceLocator.php',
		'appform' => '/Forms/AppForm.php',
		'application' => '/Application/Application.php',
		'applicationexception' => '/Application/ApplicationException.php',
		'argumentoutofrangeexception' => '/exceptions.php',
		'arraylist' => '/Collections/ArrayList.php',
		'authenticationexception' => '/Security/AuthenticationException.php',
		'autoloader' => '/Loaders/AutoLoader.php',
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
		'control' => '/Application/Control.php',
		'datagrid' => '/Web/UI/DataGrid.php',
		'datagridcolumn' => '/Web/UI/DataGrid.php',
		'debug' => '/Debug.php',
		'dibistorage' => '/Caching/DibiStorage.php',
		'directorynotfoundexception' => '/exceptions.php',
		'environment' => '/Environment.php',
		'file' => '/Forms/items.php',
		'filenotfoundexception' => '/exceptions.php',
		'filestorage' => '/Caching/FileStorage.php',
		'form' => '/Forms/Form.php',
		'formcontrol' => '/Forms/items.php',
		'forms' => '/Forms/items.php',
		'forwardingexception' => '/Application/ForwardingException.php',
		'hashtable' => '/Collections/Hashtable.php',
		'hidden' => '/Forms/items.php',
		'html' => '/Web/Html.php',
		'httprequest' => '/Web/HttpRequest.php',
		'httpresponse' => '/Web/HttpResponse.php',
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
		'invalidstateexception' => '/exceptions.php',
		'ioexception' => '/exceptions.php',
		'ipermissionassert' => '/Security/Permission.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterfactory' => '/Application/IPresenterFactory.php',
		'irouter' => '/Application/IRouter.php',
		'iservicelocator' => '/IServiceLocator.php',
		'iset' => '/Collections/ISet.php',
		'isignalreceiver' => '/Application/ISignalReceiver.php',
		'istatepersistent' => '/Application/IStatePersistent.php',
		'iview' => '/Application/IView.php',
		'keynotfoundexception' => '/Collections/Hashtable.php',
		'link' => '/Application/Link.php',
		'linkexception' => '/Application/LinkException.php',
		'memberaccessexception' => '/exceptions.php',
		'multirouter' => '/Application/MultiRouter.php',
		'multiselect' => '/Forms/items.php',
		'nette' => '/Version.php',
		'netteloader' => '/Loaders/NetteLoader.php',
		'notimplementedexception' => '/exceptions.php',
		'notsupportedexception' => '/exceptions.php',
		'object' => '/Object.php',
		'password' => '/Forms/items.php',
		'permission' => '/Security/Permission.php',
		'presenter' => '/Application/Presenter.php',
		'presentercomponent' => '/Application/PresenterComponent.php',
		'presenterfactory' => '/Application/PresenterFactory.php',
		'presenterhelpers' => '/Application/PresenterHelpers.php',
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
		'signalexception' => '/Application/SignalException.php',
		'simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'simpleloader' => '/Loaders/SimpleLoader.php',
		'simplerouter' => '/Application/SimpleRouter.php',
		'string' => '/String.php',
		'subform' => '/Forms/SubForm.php',
		'submitbutton' => '/Forms/items.php',
		'template' => '/Application/Template.php',
		'templatefilters' => '/Application/TemplateFilters.php',
		'templatestorage' => '/Caching/TemplateStorage.php',
		'text' => '/Forms/items.php',
		'textarea' => '/Forms/items.php',
		'tools' => '/Tools.php',
		'uri' => '/Web/Uri.php',
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
	 * Factory autoloader.
	 * @return NetteLoader
	 */
	public static function factory()
	{
		$loader = new self;
		$loader->base = dirname(dirname(__FILE__));
		$loader->register();
		return $loader;
	}

}
