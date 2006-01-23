<pre>
<?php
require_once '../../Nette/Debug.php';
require_once '../../Nette/Web/HttpRequest.php';

/*use Nette::Debug;*/
/*use Nette::Web::HttpRequest;*/

$request = new HttpRequest;

echo 'HttpRequest::getMethod() = '; Debug::dump($request->getMethod());
echo 'HttpRequest::getScheme() = '; Debug::dump($request->getScheme());
echo 'HttpRequest::getHost() = '; Debug::dump($request->getHost());
echo 'HttpRequest::getRawUrl() = '; Debug::dump($request->getRawUrl());
echo 'HttpRequest::getBaseUrl() = '; Debug::dump($request->getBaseUrl());
echo 'HttpRequest::getBasePath() = '; Debug::dump($request->getBasePath());
echo 'HttpRequest::getBaseScript() = '; Debug::dump($request->getBaseScript());
echo 'HttpRequest::getQuery() = '; Debug::dump($request->getQuery());
echo 'HttpRequest::getPost() = '; Debug::dump($request->getPost());
echo 'HttpRequest::getPostRaw() = '; Debug::dump($request->getPostRaw());
echo 'HttpRequest::getCookie() = '; Debug::dump($request->getCookie());
echo 'HttpRequest::getHeader() = '; Debug::dump($request->getHeader());
echo 'HttpRequest::isSecured() = '; Debug::dump($request->isSecured());
echo 'HttpRequest::isLocal() = '; Debug::dump($request->isLocal());
echo 'HttpRequest::ipHash() = '; Debug::dump($request->ipHash());

echo 'HttpRequest::isEqual() = '; Debug::dump($request->isEqual('//test/second?third'));
echo 'HttpRequest::isEqual() = '; Debug::dump($request->isEqual('http://test/second?third'));
echo 'HttpRequest::isEqual() = '; Debug::dump($request->isEqual('/second?third'));

// set
$request->setRawUrl('/test');
$request->setBaseUrl('second');
$request->isLocal(FALSE);

echo 'HttpRequest::getRawUrl() = '; Debug::dump($request->getRawUrl());
echo 'HttpRequest::getBaseUrl() = '; Debug::dump($request->getBaseUrl());
echo 'HttpRequest::getBasePath() = '; Debug::dump($request->getBasePath());
echo 'HttpRequest::isLocal() = '; Debug::dump($request->isLocal());
