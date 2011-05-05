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
		'nette\application\abortexception' => '/Application/exceptions.php',
		'nette\application\application' => '/Application/Application.php',
		'nette\application\applicationexception' => '/Application/exceptions.php',
		'nette\application\badrequestexception' => '/Application/exceptions.php',
		'nette\application\diagnostics\routingpanel' => '/Application/Diagnostics/RoutingPanel.php',
		'nette\application\forbiddenrequestexception' => '/Application/exceptions.php',
		'nette\application\invalidpresenterexception' => '/Application/exceptions.php',
		'nette\application\ipresenter' => '/Application/IPresenter.php',
		'nette\application\ipresenterfactory' => '/Application/IPresenterFactory.php',
		'nette\application\iresponse' => '/Application/IResponse.php',
		'nette\application\irouter' => '/Application/IRouter.php',
		'nette\application\presenterfactory' => '/Application/PresenterFactory.php',
		'nette\application\request' => '/Application/Request.php',
		'nette\application\responses\fileresponse' => '/Application/Responses/FileResponse.php',
		'nette\application\responses\forwardresponse' => '/Application/Responses/ForwardResponse.php',
		'nette\application\responses\jsonresponse' => '/Application/Responses/JsonResponse.php',
		'nette\application\responses\redirectresponse' => '/Application/Responses/RedirectResponse.php',
		'nette\application\responses\textresponse' => '/Application/Responses/TextResponse.php',
		'nette\application\routers\clirouter' => '/Application/Routers/CliRouter.php',
		'nette\application\routers\route' => '/Application/Routers/Route.php',
		'nette\application\routers\routelist' => '/Application/Routers/RouteList.php',
		'nette\application\routers\simplerouter' => '/Application/Routers/SimpleRouter.php',
		'nette\application\ui\badsignalexception' => '/Application/UI/BadSignalException.php',
		'nette\application\ui\control' => '/Application/UI/Control.php',
		'nette\application\ui\form' => '/Application/UI/Form.php',
		'nette\application\ui\invalidlinkexception' => '/Application/UI/InvalidLinkException.php',
		'nette\application\ui\ipartiallyrenderable' => '/Application/UI/IPartiallyRenderable.php',
		'nette\application\ui\irenderable' => '/Application/UI/IRenderable.php',
		'nette\application\ui\isignalreceiver' => '/Application/UI/ISignalReceiver.php',
		'nette\application\ui\istatepersistent' => '/Application/UI/IStatePersistent.php',
		'nette\application\ui\link' => '/Application/UI/Link.php',
		'nette\application\ui\presenter' => '/Application/UI/Presenter.php',
		'nette\application\ui\presentercomponent' => '/Application/UI/PresenterComponent.php',
		'nette\application\ui\presentercomponentreflection' => '/Application/UI/PresenterComponentReflection.php',
		'nette\argumentoutofrangeexception' => '/common/exceptions.php',
		'nette\arrayhash' => '/common/ArrayHash.php',
		'nette\arraylist' => '/common/ArrayList.php',
		'nette\caching\cache' => '/Caching/Cache.php',
		'nette\caching\istorage' => '/Caching/IStorage.php',
		'nette\caching\outputhelper' => '/Caching/OutputHelper.php',
		'nette\caching\storages\devnullstorage' => '/Caching/Storages/DevNullStorage.php',
		'nette\caching\storages\filejournal' => '/Caching/Storages/FileJournal.php',
		'nette\caching\storages\filestorage' => '/Caching/Storages/FileStorage.php',
		'nette\caching\storages\ijournal' => '/Caching/Storages/IJournal.php',
		'nette\caching\storages\memcachedstorage' => '/Caching/Storages/MemcachedStorage.php',
		'nette\caching\storages\memorystorage' => '/Caching/Storages/MemoryStorage.php',
		'nette\callback' => '/common/Callback.php',
		'nette\componentmodel\component' => '/ComponentModel/Component.php',
		'nette\componentmodel\container' => '/ComponentModel/Container.php',
		'nette\componentmodel\icomponent' => '/ComponentModel/IComponent.php',
		'nette\componentmodel\icontainer' => '/ComponentModel/IContainer.php',
		'nette\componentmodel\recursivecomponentiterator' => '/ComponentModel/RecursiveComponentIterator.php',
		'nette\config\config' => '/Config/Config.php',
		'nette\config\iadapter' => '/Config/IAdapter.php',
		'nette\config\iniadapter' => '/Config/IniAdapter.php',
		'nette\config\neonadapter' => '/Config/NeonAdapter.php',
		'nette\database\connection' => '/Database/Connection.php',
		'nette\database\diagnostics\connectionpanel' => '/Database/Diagnostics/ConnectionPanel.php',
		'nette\database\drivers\mssqldriver' => '/Database/Drivers/MsSqlDriver.php',
		'nette\database\drivers\mysqldriver' => '/Database/Drivers/MySqlDriver.php',
		'nette\database\drivers\ocidriver' => '/Database/Drivers/OciDriver.php',
		'nette\database\drivers\odbcdriver' => '/Database/Drivers/OdbcDriver.php',
		'nette\database\drivers\pgsqldriver' => '/Database/Drivers/PgSqlDriver.php',
		'nette\database\drivers\sqlite2driver' => '/Database/Drivers/Sqlite2Driver.php',
		'nette\database\drivers\sqlitedriver' => '/Database/Drivers/SqliteDriver.php',
		'nette\database\isupplementaldriver' => '/Database/ISupplementalDriver.php',
		'nette\database\reflection\databasereflection' => '/Database/Reflection/DatabaseReflection.php',
		'nette\database\row' => '/Database/Row.php',
		'nette\database\sqlliteral' => '/Database/SqlLiteral.php',
		'nette\database\sqlpreprocessor' => '/Database/SqlPreprocessor.php',
		'nette\database\statement' => '/Database/Statement.php',
		'nette\database\table\activerow' => '/Database/Table/ActiveRow.php',
		'nette\database\table\groupedselection' => '/Database/Table/GroupedSelection.php',
		'nette\database\table\selection' => '/Database/Table/Selection.php',
		'nette\datetime' => '/common/DateTime.php',
		'nette\deprecatedexception' => '/common/exceptions.php',
		'nette\di\ambiguousserviceexception' => '/DI/AmbiguousServiceException.php',
		'nette\di\configurator' => '/DI/Configurator.php',
		'nette\di\container' => '/DI/Container.php',
		'nette\di\icontainer' => '/DI/IContainer.php',
		'nette\di\iservicebuilder' => '/DI/IServiceBuilder.php',
		'nette\di\servicebuilder' => '/DI/ServiceBuilder.php',
		'nette\diagnostics\bar' => '/Diagnostics/Bar.php',
		'nette\diagnostics\bluescreen' => '/Diagnostics/BlueScreen.php',
		'nette\diagnostics\debugger' => '/Diagnostics/Debugger.php',
		'nette\diagnostics\defaultbarpanel' => '/Diagnostics/DefaultBarPanel.php',
		'nette\diagnostics\firelogger' => '/Diagnostics/FireLogger.php',
		'nette\diagnostics\helpers' => '/Diagnostics/Helpers.php',
		'nette\diagnostics\ibarpanel' => '/Diagnostics/IBarPanel.php',
		'nette\diagnostics\logger' => '/Diagnostics/Logger.php',
		'nette\directorynotfoundexception' => '/common/exceptions.php',
		'nette\environment' => '/common/Environment.php',
		'nette\fatalerrorexception' => '/common/exceptions.php',
		'nette\filenotfoundexception' => '/common/exceptions.php',
		'nette\forms\container' => '/Forms/Container.php',
		'nette\forms\controlgroup' => '/Forms/ControlGroup.php',
		'nette\forms\controls\basecontrol' => '/Forms/Controls/BaseControl.php',
		'nette\forms\controls\button' => '/Forms/Controls/Button.php',
		'nette\forms\controls\checkbox' => '/Forms/Controls/Checkbox.php',
		'nette\forms\controls\hiddenfield' => '/Forms/Controls/HiddenField.php',
		'nette\forms\controls\imagebutton' => '/Forms/Controls/ImageButton.php',
		'nette\forms\controls\multiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'nette\forms\controls\radiolist' => '/Forms/Controls/RadioList.php',
		'nette\forms\controls\selectbox' => '/Forms/Controls/SelectBox.php',
		'nette\forms\controls\submitbutton' => '/Forms/Controls/SubmitButton.php',
		'nette\forms\controls\textarea' => '/Forms/Controls/TextArea.php',
		'nette\forms\controls\textbase' => '/Forms/Controls/TextBase.php',
		'nette\forms\controls\textinput' => '/Forms/Controls/TextInput.php',
		'nette\forms\controls\uploadcontrol' => '/Forms/Controls/UploadControl.php',
		'nette\forms\form' => '/Forms/Form.php',
		'nette\forms\icontrol' => '/Forms/IControl.php',
		'nette\forms\iformrenderer' => '/Forms/IFormRenderer.php',
		'nette\forms\isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'nette\forms\rendering\defaultformrenderer' => '/Forms/Rendering/DefaultFormRenderer.php',
		'nette\forms\rule' => '/Forms/Rule.php',
		'nette\forms\rules' => '/Forms/Rules.php',
		'nette\framework' => '/common/Framework.php',
		'nette\freezableobject' => '/common/FreezableObject.php',
		'nette\http\context' => '/Http/Context.php',
		'nette\http\fileupload' => '/Http/FileUpload.php',
		'nette\http\irequest' => '/Http/IRequest.php',
		'nette\http\iresponse' => '/Http/IResponse.php',
		'nette\http\isessionstorage' => '/Http/ISessionStorage.php',
		'nette\http\iuser' => '/Http/IUser.php',
		'nette\http\request' => '/Http/Request.php',
		'nette\http\requestfactory' => '/Http/RequestFactory.php',
		'nette\http\response' => '/Http/Response.php',
		'nette\http\session' => '/Http/Session.php',
		'nette\http\sessionnamespace' => '/Http/SessionNamespace.php',
		'nette\http\url' => '/Http/Url.php',
		'nette\http\urlscript' => '/Http/UrlScript.php',
		'nette\http\user' => '/Http/User.php',
		'nette\ifreezable' => '/common/IFreezable.php',
		'nette\image' => '/common/Image.php',
		'nette\invalidargumentexception' => '/common/exceptions.php',
		'nette\invalidstateexception' => '/common/exceptions.php',
		'nette\ioexception' => '/common/exceptions.php',
		'nette\iterators\cachingiterator' => '/Iterators/CachingIterator.php',
		'nette\iterators\filter' => '/Iterators/Filter.php',
		'nette\iterators\instancefilter' => '/Iterators/InstanceFilter.php',
		'nette\iterators\mapper' => '/Iterators/Mapper.php',
		'nette\iterators\recursivefilter' => '/Iterators/RecursiveFilter.php',
		'nette\iterators\recursor' => '/Iterators/Recursor.php',
		'nette\latte\defaultmacros' => '/Latte/DefaultMacros.php',
		'nette\latte\engine' => '/Latte/Engine.php',
		'nette\latte\htmlnode' => '/Latte/HtmlNode.php',
		'nette\latte\macronode' => '/Latte/MacroNode.php',
		'nette\latte\parseexception' => '/Latte/ParseException.php',
		'nette\latte\parser' => '/Latte/Parser.php',
		'nette\loaders\autoloader' => '/Loaders/AutoLoader.php',
		'nette\loaders\netteloader' => '/Loaders/NetteLoader.php',
		'nette\loaders\robotloader' => '/Loaders/RobotLoader.php',
		'nette\localization\itranslator' => '/Localization/ITranslator.php',
		'nette\mail\imailer' => '/Mail/IMailer.php',
		'nette\mail\message' => '/Mail/Message.php',
		'nette\mail\mimepart' => '/Mail/MimePart.php',
		'nette\mail\sendmailmailer' => '/Mail/SendmailMailer.php',
		'nette\mail\smtpexception' => '/Mail/SmtpMailer.php',
		'nette\mail\smtpmailer' => '/Mail/SmtpMailer.php',
		'nette\memberaccessexception' => '/common/exceptions.php',
		'nette\notimplementedexception' => '/common/exceptions.php',
		'nette\notsupportedexception' => '/common/exceptions.php',
		'nette\object' => '/common/Object.php',
		'nette\objectmixin' => '/common/ObjectMixin.php',
		'nette\outofrangeexception' => '/common/exceptions.php',
		'nette\reflection\annotation' => '/Reflection/Annotation.php',
		'nette\reflection\annotationsparser' => '/Reflection/AnnotationsParser.php',
		'nette\reflection\classtype' => '/Reflection/ClassType.php',
		'nette\reflection\extension' => '/Reflection/Extension.php',
		'nette\reflection\globalfunction' => '/Reflection/GlobalFunction.php',
		'nette\reflection\iannotation' => '/Reflection/IAnnotation.php',
		'nette\reflection\method' => '/Reflection/Method.php',
		'nette\reflection\parameter' => '/Reflection/Parameter.php',
		'nette\reflection\property' => '/Reflection/Property.php',
		'nette\security\authenticationexception' => '/Security/AuthenticationException.php',
		'nette\security\iauthenticator' => '/Security/IAuthenticator.php',
		'nette\security\iauthorizator' => '/Security/IAuthorizator.php',
		'nette\security\identity' => '/Security/Identity.php',
		'nette\security\iidentity' => '/Security/IIdentity.php',
		'nette\security\iresource' => '/Security/IResource.php',
		'nette\security\irole' => '/Security/IRole.php',
		'nette\security\permission' => '/Security/Permission.php',
		'nette\security\simpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nette\staticclassexception' => '/common/exceptions.php',
		'nette\templating\defaulthelpers' => '/Templating/DefaultHelpers.php',
		'nette\templating\filetemplate' => '/Templating/FileTemplate.php',
		'nette\templating\filterexception' => '/Templating/FilterException.php',
		'nette\templating\ifiletemplate' => '/Templating/IFileTemplate.php',
		'nette\templating\itemplate' => '/Templating/ITemplate.php',
		'nette\templating\phpfilestorage' => '/Templating/PhpFileStorage.php',
		'nette\templating\template' => '/Templating/Template.php',
		'nette\unexpectedvalueexception' => '/common/exceptions.php',
		'nette\unknownimagefileexception' => '/common/Image.php',
		'nette\utils\arrays' => '/Utils/Arrays.php',
		'nette\utils\criticalsection' => '/Utils/CriticalSection.php',
		'nette\utils\finder' => '/Utils/Finder.php',
		'nette\utils\html' => '/Utils/Html.php',
		'nette\utils\json' => '/Utils/Json.php',
		'nette\utils\jsonexception' => '/Utils/Json.php',
		'nette\utils\limitedscope' => '/Utils/LimitedScope.php',
		'nette\utils\mimetypedetector' => '/Utils/MimeTypeDetector.php',
		'nette\utils\neon' => '/Utils/Neon.php',
		'nette\utils\neonexception' => '/Utils/Neon.php',
		'nette\utils\paginator' => '/Utils/Paginator.php',
		'nette\utils\regexpexception' => '/Utils/Strings.php',
		'nette\utils\safestream' => '/Utils/SafeStream.php',
		'nette\utils\strings' => '/Utils/Strings.php',
		'nette\utils\tokenizer' => '/Utils/Tokenizer.php',
		'nette\utils\tokenizerexception' => '/Utils/Tokenizer.php',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new static;
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
			Nette\Utils\LimitedScope::load(NETTE_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}
