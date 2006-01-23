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

    private $list = array(
        'ambiguousserviceexception' => '/ServiceLocator.php',
        'argumentoutofrangeexception' => '/exceptions.php',
        'arraylist' => '/Collections/ArrayList.php',
        'authenticationexception' => '/Security/AuthenticationException.php',
        'autoloader' => '/Loaders/AutoLoader.php',
        'collection' => '/Collections/Collection.php',
        'component' => '/Component.php',
        'componentcontainer' => '/ComponentContainer.php',
        'config' => '/Config.php',
        'configadapter_ini' => '/ConfigAdapters.php',
        'configadapter_xml' => '/ConfigAdapters.php',
        'debug' => '/Debug.php',
        'directorynotfoundexception' => '/exceptions.php',
        'environment' => '/Environment.php',
        'filenotfoundexception' => '/exceptions.php',
        'hashtable' => '/Collections/Hashtable.php',
        'html' => '/Web/Html.php',
        'httprequest' => '/Web/HttpRequest.php',
        'httpresponse' => '/Web/HttpResponse.php',
        'iauthenticator' => '/Security/IAuthenticator.php',
        'iauthorizator' => '/Security/IAuthorizator.php',
        'icausedexception' => '/ICausedException.php',
        'icollection' => '/Collections/ICollection.php',
        'icomponent' => '/IComponent.php',
        'icomponentcontainer' => '/IComponentContainer.php',
        'idebuggable' => '/IDebuggable.php',
        'identity' => '/Security/Identity.php',
        'ihttprequest' => '/Web/IHttpRequest.php',
        'ihttpresponse' => '/Web/IHttpResponse.php',
        'iidentity' => '/Security/IIdentity.php',
        'ilist' => '/Collections/IList.php',
        'imap' => '/Collections/IMap.php',
        'invalidstateexception' => '/exceptions.php',
        'ioexception' => '/exceptions.php',
        'ipermissionassert' => '/Security/Permission.php',
        'iservicelocator' => '/IServiceLocator.php',
        'iset' => '/Collections/ISet.php',
        'keynotfoundexception' => '/Collections/Hashtable.php',
        'memberaccessexception' => '/exceptions.php',
        'nette' => '/Version.php',
        'netteloader' => '/Loaders/NetteLoader.php',
        'notimplementedexception' => '/exceptions.php',
        'notsupportedexception' => '/exceptions.php',
        'object' => '/Object.php',
        'permission' => '/Security/Permission.php',
        'robotloader' => '/Loaders/RobotLoader.php',
        'safestream' => '/IO/SafeStream.php',
        'servicelocator' => '/ServiceLocator.php',
        'session' => '/Web/Session.php',
        'sessionexception' => '/Web/Session.php',
        'sessionnamespace' => '/Web/SessionNamespace.php',
        'set' => '/Collections/Set.php',
        'simpleauthenticator' => '/Security/SimpleAuthenticator.php',
        'simpleloader' => '/Loaders/SimpleLoader.php',
        'string' => '/String.php',
        'tools' => '/Tools.php',
        'user' => '/Web/User.php',
    );



    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     * @return void
     */
    public function __construct()
    {
        $this->base = dirname(dirname(__FILE__));
    }



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
        }
    }



    /**/
    public static function factory($config = NULL, $class = NULL)
    {
        parent::factory($config, __CLASS__);
    }
    /**/

}
