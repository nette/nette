<h1>Nette::Web::HttpRequest test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::HttpRequest;*/

$request = new HttpRequest;

echo 'HttpRequest::getMethod() = '; Debug::dump($request->getMethod());
echo 'HttpRequest::getUri() = '; Debug::dump($request->getUri());
echo 'HttpRequest::getUri()->authority = '; Debug::dump($request->getUri()->authority);
echo 'HttpRequest::getUri()->hostUri = '; Debug::dump($request->getUri()->hostUri);
echo 'HttpRequest::getUri()->baseUri = '; Debug::dump($request->getUri()->baseUri);
echo 'HttpRequest::getUri()->relativeUri = '; Debug::dump($request->getUri()->relativeUri);
echo 'HttpRequest::getUri()->absoluteUri = '; Debug::dump($request->getUri()->absoluteUri);
echo 'HttpRequest::getOriginalUri() = '; Debug::dump($request->getOriginalUri());
echo 'HttpRequest::getQuery() = '; Debug::dump($request->getQuery());
echo 'HttpRequest::getPost() = '; Debug::dump($request->getPost());
echo 'HttpRequest::getPostRaw() = '; Debug::dump($request->getPostRaw());
echo 'HttpRequest::getFiles() = '; Debug::dump($request->getFiles());
echo 'HttpRequest::getCookies() = '; Debug::dump($request->getCookies());
echo 'HttpRequest::getHeaders() = '; Debug::dump($request->getHeaders());
echo 'HttpRequest::isSecured() = '; Debug::dump($request->isSecured());
echo 'HttpRequest::isLocal() = '; Debug::dump($request->isLocal());
echo 'HttpRequest::ipHash() = '; Debug::dump($request->ipHash());

echo 'HttpRequest::isEqual() = '; Debug::dump($request->uri->isEqual('//test/second?third'));
echo 'HttpRequest::isEqual() = '; Debug::dump($request->uri->isEqual('http://test/second?third'));
echo 'HttpRequest::isEqual() = '; Debug::dump($request->uri->isEqual('/second?third'));

// set
$uri = $request->uri;
$uri->path = '/test';
$uri->basePath = '/second';
$request->setUri($uri);
$request->isLocal(FALSE);

echo 'HttpRequest::getUri() = '; Debug::dump($request->getUri());
echo 'HttpRequest::getUri()->authority = '; Debug::dump($request->getUri()->authority);
echo 'HttpRequest::getUri()->hostUri = '; Debug::dump($request->getUri()->hostUri);
echo 'HttpRequest::getUri()->baseUri = '; Debug::dump($request->getUri()->baseUri);
echo 'HttpRequest::getUri()->relativeUri = '; Debug::dump($request->getUri()->relativeUri);
echo 'HttpRequest::getUri()->absoluteUri = '; Debug::dump($request->getUri()->absoluteUri);
echo 'HttpRequest::isLocal() = '; Debug::dump($request->isLocal());
