<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
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
		'argumentoutofrangeexception' => '/tools/exceptions.php',
		'deprecatedexception' => '/tools/exceptions.php',
		'directorynotfoundexception' => '/tools/exceptions.php',
		'fatalerrorexception' => '/tools/exceptions.php',
		'filenotfoundexception' => '/tools/exceptions.php',
		'invalidstateexception' => '/tools/exceptions.php',
		'ioexception' => '/tools/exceptions.php',
		'memberaccessexception' => '/tools/exceptions.php',
		'nette\ambiguousserviceexception' => '/Injection/AmbiguousServiceException.php',
		'nette\application\abortexception' => '/Application/exceptions/AbortException.php',
		'nette\application\appform' => '/Application/AppForm.php',
		'nette\application\application' => '/Application/Application.php',
		'nette\application\applicationexception' => '/Application/exceptions/ApplicationException.php',
		'nette\application\badrequestexception' => '/Application/exceptions/BadRequestException.php',
		'nette\application\badsignalexception' => '/Application/exceptions/BadSignalException.php',
		'nette\application\clirouter' => '/Application/Routers/CliRouter.php',
		'nette\application\control' => '/Application/Control.php',
		'nette\application\downloadresponse' => '/Application/Responses/DownloadResponse.php',
		'nette\application\forbiddenrequestexception' => '/Application/exceptions/ForbiddenRequestException.php',
		'nette\application\forwardingresponse' => '/Application/Responses/ForwardingResponse.php',
		'nette\application\invalidlinkexception' => '/Application/exceptions/InvalidLinkException.php',
		'nette\application\invalidpresenterexception' => '/Application/exceptions/InvalidPresenterException.php',
		'nette\application\ipartiallyrenderable' => '/Application/IPartiallyRenderable.php',
		'nette\application\ipresenter' => '/Application/IPresenter.php',
		'nette\application\ipresenterfactory' => '/Application/IPresenterFactory.php',
		'nette\application\ipresenterresponse' => '/Application/IPresenterResponse.php',
		'nette\application\irenderable' => '/Application/IRenderable.php',
		'nette\application\irouter' => '/Application/IRouter.php',
		'nette\application\isignalreceiver' => '/Application/ISignalReceiver.php',
		'nette\application\istatepersistent' => '/Application/IStatePersistent.php',
		'nette\application\jsonresponse' => '/Application/Responses/JsonResponse.php',
		'nette\application\link' => '/Application/Link.php',
		'nette\application\multirouter' => '/Application/Routers/MultiRouter.php',
		'nette\application\presenter' => '/Application/Presenter.php',
		'nette\application\presentercomponent' => '/Application/PresenterComponent.php',
		'nette\application\presentercomponentreflection' => '/Application/PresenterComponentReflection.php',
		'nette\application\presenterfactory' => '/Application/PresenterFactory.php',
		'nette\application\presenterrequest' => '/Application/PresenterRequest.php',
		'nette\application\redirectingresponse' => '/Application/Responses/RedirectingResponse.php',
		'nette\application\renderresponse' => '/Application/Responses/RenderResponse.php',
		'nette\application\route' => '/Application/Routers/Route.php',
		'nette\application\routingdebugger' => '/Application/Diagnostics/RoutingDebugger.php',
		'nette\application\simplerouter' => '/Application/Routers/SimpleRouter.php',
		'nette\arrayhash' => '/tools/ArrayHash.php',
		'nette\arraylist' => '/tools/ArrayList.php',
		'nette\arraytools' => '/tools/ArrayTools.php',
		'nette\caching\cache' => '/Caching/Cache.php',
		'nette\caching\dummystorage' => '/Caching/DummyStorage.php',
		'nette\caching\filejournal' => '/Caching/FileJournal.php',
		'nette\caching\filestorage' => '/Caching/FileStorage.php',
		'nette\caching\icachejournal' => '/Caching/ICacheJournal.php',
		'nette\caching\icachestorage' => '/Caching/ICacheStorage.php',
		'nette\caching\memcachedstorage' => '/Caching/MemcachedStorage.php',
		'nette\caching\memorystorage' => '/Caching/MemoryStorage.php',
		'nette\callback' => '/tools/Callback.php',
		'nette\callbackfilteriterator' => '/tools/iterators/CallbackFilterIterator.php',
		'nette\component' => '/ComponentModel/Component.php',
		'nette\componentcontainer' => '/ComponentModel/ComponentContainer.php',
		'nette\config\config' => '/Config/Config.php',
		'nette\config\configadapterini' => '/Config/ConfigAdapterIni.php',
		'nette\config\configadapterneon' => '/Config/ConfigAdapterNeon.php',
		'nette\config\iconfigadapter' => '/Config/IConfigAdapter.php',
		'nette\configurator' => '/Environment/Configurator.php',
		'nette\context' => '/Injection/Context.php',
		'nette\criticalsection' => '/tools/CriticalSection.php',
		'nette\database\connection' => '/Database/Connection.php',
		'nette\database\databasepanel' => '/Database/Diagnostics/DatabasePanel.php',
		'nette\database\drivers\pdomssqldriver' => '/Database/Drivers/PdoMsSqlDriver.php',
		'nette\database\drivers\pdomysqldriver' => '/Database/Drivers/PdoMySqlDriver.php',
		'nette\database\drivers\pdoocidriver' => '/Database/Drivers/PdoOciDriver.php',
		'nette\database\drivers\pdoodbcdriver' => '/Database/Drivers/PdoOdbcDriver.php',
		'nette\database\drivers\pdopgsqldriver' => '/Database/Drivers/PdoPgSqlDriver.php',
		'nette\database\drivers\pdosqlite2driver' => '/Database/Drivers/PdoSqlite2Driver.php',
		'nette\database\drivers\pdosqlitedriver' => '/Database/Drivers/PdoSqliteDriver.php',
		'nette\database\isupplementaldriver' => '/Database/ISupplementalDriver.php',
		'nette\database\reflection\databasereflection' => '/Database/Reflection/DatabaseReflection.php',
		'nette\database\row' => '/Database/Row.php',
		'nette\database\selector\groupedtableselection' => '/Database/Selector/GroupedTableSelection.php',
		'nette\database\selector\tablerow' => '/Database/Selector/TableRow.php',
		'nette\database\selector\tableselection' => '/Database/Selector/TableSelection.php',
		'nette\database\sqlliteral' => '/Database/SqlLiteral.php',
		'nette\database\sqlpreprocessor' => '/Database/SqlPreprocessor.php',
		'nette\database\statement' => '/Database/Statement.php',
		'nette\datetime' => '/tools/DateTime.php',
		'nette\debug' => '/Diagnostics/Debug.php',
		'nette\debughelpers' => '/Diagnostics/DebugHelpers.php',
		'nette\debugpanel' => '/Diagnostics/DebugPanel.php',
		'nette\environment' => '/Environment/Environment.php',
		'nette\finder' => '/tools/Finder.php',
		'nette\forms\button' => '/Forms/Controls/Button.php',
		'nette\forms\checkbox' => '/Forms/Controls/Checkbox.php',
		'nette\forms\defaultformrenderer' => '/Forms/Renderers/DefaultFormRenderer.php',
		'nette\forms\fileupload' => '/Forms/Controls/FileUpload.php',
		'nette\forms\form' => '/Forms/Form.php',
		'nette\forms\formcontainer' => '/Forms/FormContainer.php',
		'nette\forms\formcontrol' => '/Forms/Controls/FormControl.php',
		'nette\forms\formgroup' => '/Forms/FormGroup.php',
		'nette\forms\hiddenfield' => '/Forms/Controls/HiddenField.php',
		'nette\forms\iformcontrol' => '/Forms/IFormControl.php',
		'nette\forms\iformrenderer' => '/Forms/IFormRenderer.php',
		'nette\forms\imagebutton' => '/Forms/Controls/ImageButton.php',
		'nette\forms\isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'nette\forms\multiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'nette\forms\radiolist' => '/Forms/Controls/RadioList.php',
		'nette\forms\rule' => '/Forms/Rule.php',
		'nette\forms\rules' => '/Forms/Rules.php',
		'nette\forms\selectbox' => '/Forms/Controls/SelectBox.php',
		'nette\forms\submitbutton' => '/Forms/Controls/SubmitButton.php',
		'nette\forms\textarea' => '/Forms/Controls/TextArea.php',
		'nette\forms\textbase' => '/Forms/Controls/TextBase.php',
		'nette\forms\textinput' => '/Forms/Controls/TextInput.php',
		'nette\framework' => '/tools/Framework.php',
		'nette\freezableobject' => '/tools/FreezableObject.php',
		'nette\genericrecursiveiterator' => '/tools/iterators/GenericRecursiveIterator.php',
		'nette\icomponent' => '/ComponentModel/IComponent.php',
		'nette\icomponentcontainer' => '/ComponentModel/IComponentContainer.php',
		'nette\icontext' => '/Injection/IContext.php',
		'nette\idebugpanel' => '/Diagnostics/IDebugPanel.php',
		'nette\ifreezable' => '/tools/IFreezable.php',
		'nette\image' => '/tools/Image.php',
		'nette\instancefilteriterator' => '/tools/iterators/InstanceFilterIterator.php',
		'nette\itranslator' => '/Localization/ITranslator.php',
		'nette\json' => '/tools/Json.php',
		'nette\jsonexception' => '/tools/JsonException.php',
		'nette\loaders\autoloader' => '/Loaders/AutoLoader.php',
		'nette\loaders\limitedscope' => '/Loaders/LimitedScope.php',
		'nette\loaders\netteloader' => '/Loaders/NetteLoader.php',
		'nette\loaders\robotloader' => '/Loaders/RobotLoader.php',
		'nette\mail\imailer' => '/Mail/IMailer.php',
		'nette\mail\mail' => '/Mail/Mail.php',
		'nette\mail\mailmimepart' => '/Mail/MailMimePart.php',
		'nette\mail\sendmailmailer' => '/Mail/SendmailMailer.php',
		'nette\mail\smtpexception' => '/Mail/SmtpException.php',
		'nette\mail\smtpmailer' => '/Mail/SmtpMailer.php',
		'nette\mapiterator' => '/tools/iterators/MapIterator.php',
		'nette\mimetypedetector' => '/tools/MimeTypeDetector.php',
		'nette\neon' => '/tools/Neon.php',
		'nette\neonexception' => '/tools/Neon.php',
		'nette\object' => '/tools/Object.php',
		'nette\objectmixin' => '/tools/ObjectMixin.php',
		'nette\paginator' => '/tools/Paginator.php',
		'nette\recursivecallbackfilteriterator' => '/tools/iterators/RecursiveCallbackFilterIterator.php',
		'nette\recursivecomponentiterator' => '/ComponentModel/RecursiveComponentIterator.php',
		'nette\reflection\annotation' => '/Reflection/Annotation.php',
		'nette\reflection\annotationsparser' => '/Reflection/AnnotationsParser.php',
		'nette\reflection\classreflection' => '/Reflection/ClassReflection.php',
		'nette\reflection\extensionreflection' => '/Reflection/ExtensionReflection.php',
		'nette\reflection\functionreflection' => '/Reflection/FunctionReflection.php',
		'nette\reflection\iannotation' => '/Reflection/IAnnotation.php',
		'nette\reflection\methodreflection' => '/Reflection/MethodReflection.php',
		'nette\reflection\parameterreflection' => '/Reflection/ParameterReflection.php',
		'nette\reflection\propertyreflection' => '/Reflection/PropertyReflection.php',
		'nette\regexpexception' => '/tools/RegexpException.php',
		'nette\safestream' => '/tools/SafeStream.php',
		'nette\security\authenticationexception' => '/Security/AuthenticationException.php',
		'nette\security\iauthenticator' => '/Security/IAuthenticator.php',
		'nette\security\iauthorizator' => '/Security/IAuthorizator.php',
		'nette\security\identity' => '/Security/Identity.php',
		'nette\security\iidentity' => '/Security/IIdentity.php',
		'nette\security\iresource' => '/Security/IResource.php',
		'nette\security\irole' => '/Security/IRole.php',
		'nette\security\permission' => '/Security/Permission.php',
		'nette\security\simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nette\smartcachingiterator' => '/tools/iterators/SmartCachingIterator.php',
		'nette\string' => '/tools/String.php',
		'nette\templates\cachinghelper' => '/Latte/CachingHelper.php',
		'nette\templates\filetemplate' => '/Templates/FileTemplate.php',
		'nette\templates\ifiletemplate' => '/Templates/IFileTemplate.php',
		'nette\templates\itemplate' => '/Templates/ITemplate.php',
		'nette\templates\latteexception' => '/Latte/LatteException.php',
		'nette\templates\lattefilter' => '/Latte/LatteFilter.php',
		'nette\templates\lattemacros' => '/Latte/LatteMacros.php',
		'nette\templates\template' => '/Templates/Template.php',
		'nette\templates\templatecachestorage' => '/Templates/TemplateCacheStorage.php',
		'nette\templates\templateexception' => '/Templates/TemplateException.php',
		'nette\templates\templatefilters' => '/Templates/TemplateFilters.php',
		'nette\templates\templatehelpers' => '/Templates/TemplateHelpers.php',
		'nette\tokenizer' => '/tools/Tokenizer.php',
		'nette\tokenizerexception' => '/tools/TokenizerException.php',
		'nette\tools' => '/tools/Tools.php',
		'nette\web\html' => '/tools/Html.php',
		'nette\web\httpcontext' => '/Http/HttpContext.php',
		'nette\web\httprequest' => '/Http/HttpRequest.php',
		'nette\web\httprequestfactory' => '/Http/HttpRequestFactory.php',
		'nette\web\httpresponse' => '/Http/HttpResponse.php',
		'nette\web\httpuploadedfile' => '/Http/HttpUploadedFile.php',
		'nette\web\ihttprequest' => '/Http/IHttpRequest.php',
		'nette\web\ihttpresponse' => '/Http/IHttpResponse.php',
		'nette\web\isessionstorage' => '/Http/ISessionStorage.php',
		'nette\web\iuser' => '/Http/IUser.php',
		'nette\web\session' => '/Http/Session.php',
		'nette\web\sessionnamespace' => '/Http/SessionNamespace.php',
		'nette\web\uri' => '/Http/Uri.php',
		'nette\web\uriscript' => '/Http/UriScript.php',
		'nette\web\user' => '/Http/User.php',
		'notimplementedexception' => '/tools/exceptions.php',
		'notsupportedexception' => '/tools/exceptions.php',
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
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(NETTE_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}
