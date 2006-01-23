<?php

require_once '../../Nette/Object.php';

echo '<h1>Nette::Object events test</h1>';
echo "<pre>\n";



function handler($val)
{
    echo "Hello $val\n";
}




class Test extends /*Nette::*/Object
{
    private $onClick1;

    protected $onClick2;

    public $onClick3;

    public $onClick4 = 'nonevent';

    static public $onClick5;
}



$obj = new Test;
echo "\n\n<h2>Invoking events</h2>\n";

try {
    echo 'private ';

    $obj->onClick1(123);
    echo "SUCCESS\n";

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


try {
    echo 'protected ';

    $obj->onClick2(123);
    echo "SUCCESS\n";

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


try {
    echo 'public ';

    $obj->onClick3(123);
    echo "SUCCESS\n";

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


try {
    echo 'nonevent ';

    $obj->onClick4(123);
    echo "SUCCESS\n";

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


try {
    echo 'static public ';

    $obj->onClick5(123);
    echo "SUCCESS\n";

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}



echo "\n\n<h2>Attaching handler</h2>\n";

$obj->onClick3[] = 'handler';

echo "Invoking: ", $obj->onClick3('vole'), "\n";
