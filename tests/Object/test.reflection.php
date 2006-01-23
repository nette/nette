<?php

require_once '../../Nette/Debug.php';
require_once '../../Nette/Object.php';

/*use Nette::Debug;*/

echo '<h1>Nette::Object reflection test</h1>';
echo "<pre>\n";



class Test extends /*Nette::*/Object
{
    public $a;

    static public $b;

    static function c()
    {}

}



$obj = new Test();

echo "Class: {$obj->Class}\n";

echo "Methods:\n";

Debug::dump($obj->Reflection->getMethods());
