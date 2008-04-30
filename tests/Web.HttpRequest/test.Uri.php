<h1>Nette::Web::Uri test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::Uri;*/

$uri = new Uri('http://username:password@hostname:60/path?arg=value#anchor');

echo '$uri = '; Debug::dump($uri);
echo '$uri->authority = '; Debug::dump($uri->authority);
echo '$uri->hostUri = '; Debug::dump($uri->hostUri);
echo '$uri->absoluteUri = '; Debug::dump($uri->absoluteUri);


/*
$uri = new Uri('mailto:john@doe.com');

echo '$uri = '; Debug::dump($uri);
echo '$uri->authority = '; Debug::dump($uri->authority);
echo '$uri->hostUri = '; Debug::dump($uri->hostUri);
echo '$uri->absoluteUri = '; Debug::dump($uri->absoluteUri);
*/