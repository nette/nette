<?php

$a = 1;
${'a'} = "{$a} ${a}";

if ($a) {
    class /*Nette::*/Object
    {
    }
}