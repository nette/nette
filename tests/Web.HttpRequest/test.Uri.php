<h1>Nette::Web::Uri test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::Uri;*/

echo "<h1>Http scheme</h1>\n";

$uri = new Uri('http://username:password@hostname:60/path?arg=value#anchor');

echo '$uri = '; Debug::dump($uri);
echo '$uri->authority = '; Debug::dump($uri->authority);
echo '$uri->hostUri = '; Debug::dump($uri->hostUri);
echo '$uri->absoluteUri = '; Debug::dump($uri->absoluteUri);
echo '(string) $uri = '; Debug::dump((string) $uri);


echo "<h1>Ftp scheme</h1>\n";

$uri = new Uri('ftp://ftp.is.co.za/rfc/rfc3986.txt');

echo '$uri = '; Debug::dump($uri);
echo '$uri->authority = '; Debug::dump($uri->authority);
echo '$uri->hostUri = '; Debug::dump($uri->hostUri);
echo '$uri->absoluteUri = '; Debug::dump($uri->absoluteUri);


echo "<h1>File scheme</h1>\n";

$uri = new Uri('file://localhost/D:/dokumentace/rfc3986.txt');

echo '$uri = '; Debug::dump($uri);
echo '(string) $uri = '; Debug::dump((string) $uri);


echo "<h1>File scheme II.</h1>\n";

$uri = new Uri('file:///D:/dokumentace/rfc3986.txt');

echo '$uri = '; Debug::dump($uri);
echo '(string) $uri = '; Debug::dump((string) $uri);


echo "<h1>Malformed URI</h1>\n";

try {
	$uri = new Uri(':');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


/*
// not supported by parse_url()
$uri = new Uri('mailto:john@doe.com');

echo '$uri = '; Debug::dump($uri);
echo '(string) $uri = '; Debug::dump((string) $uri);
echo '$uri->authority = '; Debug::dump($uri->authority);
echo '$uri->hostUri = '; Debug::dump($uri->hostUri);
echo '$uri->absoluteUri = '; Debug::dump($uri->absoluteUri);
*/