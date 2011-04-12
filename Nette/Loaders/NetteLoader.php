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
		'argumentoutofrangeexception' => '/common/exceptions.php',
		'deprecatedexception' => '/common/exceptions.php',
		'directorynotfoundexception' => '/common/exceptions.php',
		'fatalerrorexception' => '/common/exceptions.php',
		'filenotfoundexception' => '/common/exceptions.php',
		'invalidstateexception' => '/common/exceptions.php',
		'ioexception' => '/common/exceptions.php',
		'memberaccessexception' => '/common/exceptions.php',
		'nette\ambiguousserviceexception' => '/DI/AmbiguousServiceException.php',
		'nette\application\abortexception' => '/Application/exceptions.php',
		'nette\application\appform' => '/Application/UI/Form.php',
		'nette\application\application' => '/Application/Application.php',
		'nette\application\applicationexception' => '/Application/exceptions.php',
		'nette\application\badrequestexception' => '/Application/exceptions.php',
		'nette\application\badsignalexception' => '/Application/UI/BadSignalException.php',
		'nette\application\clirouter' => '/Application/Routers/CliRouter.php',
		'nette\application\control' => '/Application/UI/Control.php',
		'nette\application\downloadresponse' => '/Application/Responses/FileResponse.php',
		'nette\application\forbiddenrequestexception' => '/Application/exceptions.php',
		'nette\application\forwardingresponse' => '/Application/Responses/ForwardResponse.php',
		'nette\application\invalidlinkexception' => '/Application/UI/InvalidLinkException.php',
		'nette\application\invalidpresenterexception' => '/Application/exceptions.php',
		'nette\application\ipartiallyrenderable' => '/Application/UI/IPartiallyRenderable.php',
		'nette\application\ipresenter' => '/Application/IPresenter.php',
		'nette\application\ipresenterfactory' => '/Application/IPresenterFactory.php',
		'nette\application\ipresenterresponse' => '/Application/IResponse.php',
		'nette\application\irenderable' => '/Application/UI/IRenderable.php',
		'nette\application\irouter' => '/Application/IRouter.php',
		'nette\application\isignalreceiver' => '/Application/UI/ISignalReceiver.php',
		'nette\application\istatepersistent' => '/Application/UI/IStatePersistent.php',
		'nette\application\jsonresponse' => '/Application/Responses/JsonResponse.php',
		'nette\application\link' => '/Application/UI/Link.php',
		'nette\application\multirouter' => '/Application/Routers/RouteList.php',
		'nette\application\presenter' => '/Application/UI/Presenter.php',
		'nette\application\presentercomponent' => '/Application/UI/PresenterComponent.php',
		'nette\application\presentercomponentreflection' => '/Application/UI/PresenterComponentReflection.php',
		'nette\application\presenterfactory' => '/Application/PresenterFactory.php',
		'nette\application\presenterrequest' => '/Application/Request.php',
		'nette\application\redirectingresponse' => '/Application/Responses/RedirectResponse.php',
		'nette\application\renderresponse' => '/Application/Responses/TextResponse.php',
		'nette\application\route' => '/Application/Routers/Route.php',
		'nette\application\routingdebugger' => '/Application/Diagnostics/RoutingPanel.php',
		'nette\application\simplerouter' => '/Application/Routers/SimpleRouter.php',
		'nette\arrayhash' => '/common/ArrayHash.php',
		'nette\arraylist' => '/common/ArrayList.php',
		'nette\arraytools' => '/common/ArrayUtils.php',
		'nette\caching\cache' => '/Caching/Cache.php',
		'nette\caching\dummystorage' => '/Caching/Storages/DevNullStorage.php',
		'nette\caching\filejournal' => '/Caching/Storages/FileJournal.php',
		'nette\caching\filestorage' => '/Caching/Storages/FileStorage.php',
		'nette\caching\icachejournal' => '/Caching/Storages/IJournal.php',
		'nette\caching\icachestorage' => '/Caching/IStorage.php',
		'nette\caching\memcachedstorage' => '/Caching/Storages/MemcachedStorage.php',
		'nette\caching\memorystorage' => '/Caching/Storages/MemoryStorage.php',
		'nette\callback' => '/common/Callback.php',
		'nette\callbackfilteriterator' => '/Iterators/Filter.php',
		'nette\component' => '/ComponentModel/Component.php',
		'nette\componentcontainer' => '/ComponentModel/Container.php',
		'nette\config\config' => '/Config/Config.php',
		'nette\config\configadapterini' => '/Config/IniAdapter.php',
		'nette\config\configadapterneon' => '/Config/NeonAdapter.php',
		'nette\config\iconfigadapter' => '/Config/IAdapter.php',
		'nette\configurator' => '/DI/Configurator.php',
		'nette\context' => '/DI/Context.php',
		'nette\criticalsection' => '/Utils/CriticalSection.php',
		'nette\database\connection' => '/Database/Connection.php',
		'nette\database\databasepanel' => '/Database/Diagnostics/ConnectionPanel.php',
		'nette\database\drivers\pdomssqldriver' => '/Database/Drivers/MsSqlDriver.php',
		'nette\database\drivers\pdomysqldriver' => '/Database/Drivers/MySqlDriver.php',
		'nette\database\drivers\pdoocidriver' => '/Database/Drivers/OciDriver.php',
		'nette\database\drivers\pdoodbcdriver' => '/Database/Drivers/OdbcDriver.php',
		'nette\database\drivers\pdopgsqldriver' => '/Database/Drivers/PgSqlDriver.php',
		'nette\database\drivers\pdosqlite2driver' => '/Database/Drivers/Sqlite2Driver.php',
		'nette\database\drivers\pdosqlitedriver' => '/Database/Drivers/SqliteDriver.php',
		'nette\database\isupplementaldriver' => '/Database/ISupplementalDriver.php',
		'nette\database\reflection\databasereflection' => '/Database/Reflection/DatabaseReflection.php',
		'nette\database\row' => '/Database/Row.php',
		'nette\database\selector\groupedtableselection' => '/Database/Table/GroupedSelection.php',
		'nette\database\selector\tablerow' => '/Database/Table/ActiveRow.php',
		'nette\database\selector\tableselection' => '/Database/Table/Selection.php',
		'nette\database\sqlliteral' => '/Database/SqlLiteral.php',
		'nette\database\sqlpreprocessor' => '/Database/SqlPreprocessor.php',
		'nette\database\statement' => '/Database/Statement.php',
		'nette\datetime' => '/common/DateTime.php',
		'nette\debug' => '/Diagnostics/Debugger.php',
		'nette\debughelpers' => '/Diagnostics/Helpers.php',
		'nette\debugpanel' => '/Diagnostics/Panel.php',
		'nette\environment' => '/common/Environment.php',
		'nette\finder' => '/Utils/Finder.php',
		'nette\forms\button' => '/Forms/Controls/Button.php',
		'nette\forms\checkbox' => '/Forms/Controls/Checkbox.php',
		'nette\forms\defaultformrenderer' => '/Forms/Rendering/DefaultFormRenderer.php',
		'nette\forms\fileupload' => '/Forms/Controls/UploadControl.php',
		'nette\forms\form' => '/Forms/Form.php',
		'nette\forms\formcontainer' => '/Forms/Container.php',
		'nette\forms\formcontrol' => '/Forms/Controls/BaseControl.php',
		'nette\forms\formgroup' => '/Forms/ControlGroup.php',
		'nette\forms\hiddenfield' => '/Forms/Controls/HiddenField.php',
		'nette\forms\iformcontrol' => '/Forms/IControl.php',
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
		'nette\framework' => '/common/Framework.php',
		'nette\freezableobject' => '/common/FreezableObject.php',
		'nette\genericrecursiveiterator' => '/Iterators/Recursor.php',
		'nette\icomponent' => '/ComponentModel/IComponent.php',
		'nette\icomponentcontainer' => '/ComponentModel/IContainer.php',
		'nette\icontext' => '/DI/IContext.php',
		'nette\idebugpanel' => '/Diagnostics/IPanel.php',
		'nette\ifreezable' => '/common/IFreezable.php',
		'nette\image' => '/common/Image.php',
		'nette\instancefilteriterator' => '/Iterators/InstanceFilter.php',
		'nette\itranslator' => '/Localization/ITranslator.php',
		'nette\json' => '/Utils/Json.php',
		'nette\jsonexception' => '/Utils/Json.php',
		'nette\loaders\autoloader' => '/Loaders/AutoLoader.php',
		'nette\loaders\limitedscope' => '/Utils/LimitedScope.php',
		'nette\loaders\netteloader' => '/Loaders/NetteLoader.php',
		'nette\loaders\robotloader' => '/Loaders/RobotLoader.php',
		'nette\mail\imailer' => '/Mail/IMailer.php',
		'nette\mail\mail' => '/Mail/Message.php',
		'nette\mail\mailmimepart' => '/Mail/MimePart.php',
		'nette\mail\sendmailmailer' => '/Mail/SendmailMailer.php',
		'nette\mail\smtpexception' => '/Mail/SmtpMailer.php',
		'nette\mail\smtpmailer' => '/Mail/SmtpMailer.php',
		'nette\mapiterator' => '/Iterators/Mapper.php',
		'nette\mimetypedetector' => '/Utils/MimeTypeDetector.php',
		'nette\neon' => '/Utils/Neon.php',
		'nette\neonexception' => '/Utils/Neon.php',
		'nette\object' => '/common/Object.php',
		'nette\objectmixin' => '/common/ObjectMixin.php',
		'nette\paginator' => '/Utils/Paginator.php',
		'nette\recursivecallbackfilteriterator' => '/Iterators/RecursiveFilter.php',
		'nette\recursivecomponentiterator' => '/ComponentModel/RecursiveComponentIterator.php',
		'nette\reflection\annotation' => '/Reflection/Annotation.php',
		'nette\reflection\annotationsparser' => '/Reflection/AnnotationsParser.php',
		'nette\reflection\classreflection' => '/Reflection/ClassType.php',
		'nette\reflection\extensionreflection' => '/Reflection/Extension.php',
		'nette\reflection\functionreflection' => '/Reflection/GlobalFunction.php',
		'nette\reflection\iannotation' => '/Reflection/IAnnotation.php',
		'nette\reflection\methodreflection' => '/Reflection/Method.php',
		'nette\reflection\parameterreflection' => '/Reflection/Parameter.php',
		'nette\reflection\propertyreflection' => '/Reflection/Property.php',
		'nette\regexpexception' => '/common/StringUtils.php',
		'nette\safestream' => '/Utils/SafeStream.php',
		'nette\security\authenticationexception' => '/Security/AuthenticationException.php',
		'nette\security\iauthenticator' => '/Security/IAuthenticator.php',
		'nette\security\iauthorizator' => '/Security/IAuthorizator.php',
		'nette\security\identity' => '/Security/Identity.php',
		'nette\security\iidentity' => '/Security/IIdentity.php',
		'nette\security\iresource' => '/Security/IResource.php',
		'nette\security\irole' => '/Security/IRole.php',
		'nette\security\permission' => '/Security/Permission.php',
		'nette\security\simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nette\smartcachingiterator' => '/Iterators/CachingIterator.php',
		'nette\string' => '/common/StringUtils.php',
		'nette\templates\cachinghelper' => '/Caching/OutputHelper.php',
		'nette\templates\filetemplate' => '/Templating/FileTemplate.php',
		'nette\templates\ifiletemplate' => '/Templating/IFileTemplate.php',
		'nette\templates\itemplate' => '/Templating/ITemplate.php',
		'nette\templates\latteexception' => '/Latte/ParseException.php',
		'nette\templates\lattefilter' => '/Latte/Engine.php',
		'nette\templates\lattemacros' => '/Latte/DefaultMacros.php',
		'nette\templates\template' => '/Templating/Template.php',
		'nette\templates\templatecachestorage' => '/Templating/PhpFileStorage.php',
		'nette\templates\templateexception' => '/Templating/FilterException.php',
		'nette\templates\templatehelpers' => '/Templating/DefaultHelpers.php',
		'nette\tokenizer' => '/Utils/Tokenizer.php',
		'nette\tokenizerexception' => '/Utils/Tokenizer.php',
		'nette\web\html' => '/Utils/Html.php',
		'nette\web\httpcontext' => '/Http/Context.php',
		'nette\web\httprequest' => '/Http/Request.php',
		'nette\web\httprequestfactory' => '/Http/RequestFactory.php',
		'nette\web\httpresponse' => '/Http/Response.php',
		'nette\web\httpuploadedfile' => '/Http/FileUpload.php',
		'nette\web\ihttprequest' => '/Http/IRequest.php',
		'nette\web\ihttpresponse' => '/Http/IResponse.php',
		'nette\web\isessionstorage' => '/Http/ISessionStorage.php',
		'nette\web\iuser' => '/Http/IUser.php',
		'nette\web\session' => '/Http/Session.php',
		'nette\web\sessionnamespace' => '/Http/SessionNamespace.php',
		'nette\web\uri' => '/Http/Url.php',
		'nette\web\uriscript' => '/Http/UrlScript.php',
		'nette\web\user' => '/Http/User.php',
		'notimplementedexception' => '/common/exceptions.php',
		'notsupportedexception' => '/common/exceptions.php',
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
