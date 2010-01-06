<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Loaders
 */

/*namespace Nette\Loaders;*/



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Loaders
 */
class NetteLoader extends AutoLoader
{
	/** @var NetteLoader */
	public static $instance;

	/** @var string  base file path */
	public $base;

	/** @var array */
	public $list = array(
		'abortexception' => '/Application/Exceptions/AbortException.php',
		'ambiguousserviceexception' => '/ServiceLocator.php',
		'annotation' => '/Reflection/Annotation.php',
		'annotations' => '/Annotations.php',
		'annotationsparser' => '/Reflection/AnnotationsParser.php',
		'appform' => '/Application/AppForm.php',
		'application' => '/Application/Application.php',
		'applicationexception' => '/Application/Exceptions/ApplicationException.php',
		'argumentoutofrangeexception' => '/exceptions.php',
		'arraylist' => '/Collections/ArrayList.php',
		'arraytools' => '/ArrayTools.php',
		'authenticationexception' => '/Security/AuthenticationException.php',
		'autoloader' => '/Loaders/AutoLoader.php',
		'badrequestexception' => '/Application/Exceptions/BadRequestException.php',
		'badsignalexception' => '/Application/Exceptions/BadSignalException.php',
		'basetemplate' => '/Templates/BaseTemplate.php',
		'button' => '/Forms/Controls/Button.php',
		'cache' => '/Caching/Cache.php',
		'cachinghelper' => '/Templates/Filters/CachingHelper.php',
		'checkbox' => '/Forms/Controls/Checkbox.php',
		'classreflection' => '/Reflection/ClassReflection.php',
		'clirouter' => '/Application/Routers/CliRouter.php',
		'collection' => '/Collections/Collection.php',
		'component' => '/Component.php',
		'componentcontainer' => '/ComponentContainer.php',
		'config' => '/Config/Config.php',
		'configadapterini' => '/Config/ConfigAdapterIni.php',
		'configurator' => '/Configurator.php',
		'control' => '/Application/Control.php',
		'conventionalrenderer' => '/Forms/Renderers/ConventionalRenderer.php',
		'curlybracketsfilter' => '/compatibility/OldLatteMacros.php',
		'curlybracketsmacros' => '/compatibility/OldLatteMacros.php',
		'debug' => '/Debug.php',
		'deprecatedexception' => '/exceptions.php',
		'directorynotfoundexception' => '/exceptions.php',
		'downloadresponse' => '/Application/Responses/DownloadResponse.php',
		'dummystorage' => '/Caching/DummyStorage.php',
		'environment' => '/Environment.php',
		'extensionreflection' => '/Reflection/ExtensionReflection.php',
		'fatalerrorexception' => '/exceptions.php',
		'filenotfoundexception' => '/exceptions.php',
		'filestorage' => '/Caching/FileStorage.php',
		'fileupload' => '/Forms/Controls/FileUpload.php',
		'forbiddenrequestexception' => '/Application/Exceptions/ForbiddenRequestException.php',
		'form' => '/Forms/Form.php',
		'formcontainer' => '/Forms/FormContainer.php',
		'formcontrol' => '/Forms/Controls/FormControl.php',
		'formgroup' => '/Forms/FormGroup.php',
		'forwardingresponse' => '/Application/Responses/ForwardingResponse.php',
		'framework' => '/Framework.php',
		'freezableobject' => '/FreezableObject.php',
		'ftp' => '/Web/Ftp.php',
		'ftpexception' => '/Web/Ftp.php',
		'functionreflection' => '/Reflection/FunctionReflection.php',
		'hashtable' => '/Collections/Hashtable.php',
		'hiddenfield' => '/Forms/Controls/HiddenField.php',
		'html' => '/Web/Html.php',
		'httpcontext' => '/Web/HttpContext.php',
		'httprequest' => '/Web/HttpRequest.php',
		'httpresponse' => '/Web/HttpResponse.php',
		'httpuploadedfile' => '/Web/HttpUploadedFile.php',
		'iannotation' => '/Reflection/IAnnotation.php',
		'iauthenticator' => '/Security/IAuthenticator.php',
		'iauthorizator' => '/Security/IAuthorizator.php',
		'icachestorage' => '/Caching/ICacheStorage.php',
		'icollection' => '/Collections/ICollection.php',
		'icomponent' => '/IComponent.php',
		'icomponentcontainer' => '/IComponentContainer.php',
		'iconfigadapter' => '/Config/IConfigAdapter.php',
		'idebuggable' => '/IDebuggable.php',
		'identity' => '/Security/Identity.php',
		'ifiletemplate' => '/Templates/IFileTemplate.php',
		'iformcontrol' => '/Forms/IFormControl.php',
		'iformrenderer' => '/Forms/IFormRenderer.php',
		'ihttprequest' => '/Web/IHttpRequest.php',
		'ihttpresponse' => '/Web/IHttpResponse.php',
		'iidentity' => '/Security/IIdentity.php',
		'ilist' => '/Collections/IList.php',
		'image' => '/Image.php',
		'imagebutton' => '/Forms/Controls/ImageButton.php',
		'imagemagick' => '/ImageMagick.php',
		'imailer' => '/Mail/IMailer.php',
		'imap' => '/Collections/IMap.php',
		'inamingcontainer' => '/Forms/INamingContainer.php',
		'instancefilteriterator' => '/InstanceFilterIterator.php',
		'instantclientscript' => '/Forms/Renderers/InstantClientScript.php',
		'invalidlinkexception' => '/Application/Exceptions/InvalidLinkException.php',
		'invalidpresenterexception' => '/Application/Exceptions/InvalidPresenterException.php',
		'invalidstateexception' => '/exceptions.php',
		'ioexception' => '/exceptions.php',
		'ipartiallyrenderable' => '/Application/IRenderable.php',
		'ipermissionassertion' => '/Security/IPermissionAssertion.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterloader' => '/Application/IPresenterLoader.php',
		'ipresenterresponse' => '/Application/IPresenterResponse.php',
		'irenderable' => '/Application/IRenderable.php',
		'iresource' => '/Security/IResource.php',
		'irole' => '/Security/IRole.php',
		'irouter' => '/Application/IRouter.php',
		'iservicelocator' => '/IServiceLocator.php',
		'iset' => '/Collections/ISet.php',
		'isignalreceiver' => '/Application/ISignalReceiver.php',
		'istatepersistent' => '/Application/IStatePersistent.php',
		'isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'itemplate' => '/Templates/ITemplate.php',
		'itranslator' => '/ITranslator.php',
		'iuser' => '/Web/IUser.php',
		'jsonresponse' => '/Application/Responses/JsonResponse.php',
		'keynotfoundexception' => '/Collections/Hashtable.php',
		'lattefilter' => '/Templates/Filters/LatteFilter.php',
		'lattemacros' => '/Templates/Filters/LatteMacros.php',
		'limitedscope' => '/Loaders/LimitedScope.php',
		'link' => '/Application/Link.php',
		'mail' => '/Mail/Mail.php',
		'mailmimepart' => '/Mail/MailMimePart.php',
		'memberaccessexception' => '/exceptions.php',
		'memcachedstorage' => '/Caching/MemcachedStorage.php',
		'methodparameterreflection' => '/Reflection/MethodParameterReflection.php',
		'methodreflection' => '/Reflection/MethodReflection.php',
		'multirouter' => '/Application/Routers/MultiRouter.php',
		'multiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'netteloader' => '/Loaders/NetteLoader.php',
		'notimplementedexception' => '/exceptions.php',
		'notsupportedexception' => '/exceptions.php',
		'object' => '/Object.php',
		'objectmixin' => '/ObjectMixin.php',
		'oldidentity' => '/compatibility/OldIdentity.php',
		'oldlattemacros' => '/compatibility/OldLatteMacros.php',
		'oldpresenter' => '/compatibility/OldPresenter.php',
		'paginator' => '/Paginator.php',
		'permission' => '/Security/Permission.php',
		'presenter' => '/Application/Presenter.php',
		'presentercomponent' => '/Application/PresenterComponent.php',
		'presentercomponentreflection' => '/Application/PresenterComponentReflection.php',
		'presenterloader' => '/Application/PresenterLoader.php',
		'presenterrequest' => '/Application/PresenterRequest.php',
		'propertyreflection' => '/Reflection/PropertyReflection.php',
		'radiolist' => '/Forms/Controls/RadioList.php',
		'recursivecomponentiterator' => '/ComponentContainer.php',
		'recursivehtmliterator' => '/Web/Html.php',
		'redirectingresponse' => '/Application/Responses/RedirectingResponse.php',
		'renderresponse' => '/Application/Responses/RenderResponse.php',
		'robotloader' => '/Loaders/RobotLoader.php',
		'route' => '/Application/Routers/Route.php',
		'rule' => '/Forms/Rule.php',
		'rules' => '/Forms/Rules.php',
		'safestream' => '/IO/SafeStream.php',
		'selectbox' => '/Forms/Controls/SelectBox.php',
		'sendmailmailer' => '/Mail/SendmailMailer.php',
		'servicelocator' => '/ServiceLocator.php',
		'session' => '/Web/Session.php',
		'sessionnamespace' => '/Web/SessionNamespace.php',
		'set' => '/Collections/Set.php',
		'simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'simplerouter' => '/Application/Routers/SimpleRouter.php',
		'smartcachingiterator' => '/SmartCachingIterator.php',
		'snippethelper' => '/compatibility/SnippetHelper.php',
		'string' => '/String.php',
		'submitbutton' => '/Forms/Controls/SubmitButton.php',
		'template' => '/Templates/Template.php',
		'templatecachestorage' => '/Templates/TemplateCacheStorage.php',
		'templatefilters' => '/Templates/Filters/TemplateFilters.php',
		'templatehelpers' => '/Templates/Filters/TemplateHelpers.php',
		'textarea' => '/Forms/Controls/TextArea.php',
		'textbase' => '/Forms/Controls/TextBase.php',
		'textinput' => '/Forms/Controls/TextInput.php',
		'tools' => '/Tools.php',
		'uri' => '/Web/Uri.php',
		'uriscript' => '/Web/UriScript.php',
		'user' => '/Web/User.php',
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
		$type = strtolower($type);
		if (isset($this->list[$type])) {
			LimitedScope::load($this->base . $this->list[$type]);
			self::$count++;
		}
	}

}
