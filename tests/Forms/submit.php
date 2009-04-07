<?php

$disableExit = TRUE;

$_data = array(
	'manual-rendering.php' => array('name'=>'John Doe ','age'=>'  12 ','email'=>'@','street'=>'','city'=>'','country'=>'CZ','password'=>'xxx','password2'=>'xxx','note'=>'','userid'=>'231','submit1'=>'Send',),
	'basic-example.php' => array('name'=>'John Doe ','age'=>'','email'=>'  @ ','send'=>'on','street'=>'','city'=>'','country'=>'HU','password'=>'xxx','password2'=>'','note'=>'','submit1'=>'Send','userid'=>'231',),
	'localization.php' => array('name'=>'John Doe ','age'=>'90','gender'=>'f','email'=>'david@grudl.com','street'=>'','city'=>'','country'=>'CZ','password'=>'123','password2'=>'123','note'=>'Hello"world"\\x','submit1'=>'Odeslat','userid'=>'231',),
	'custom-rendering.php' => array('name'=>'John Doe ','age'=>'9.9','email'=>'@','street'=>'','city'=>'Troubsko','country'=>'0','password'=>'xx','password2'=>'xx','note'=>'','submit1'=>'Send','userid'=>'231',),
	'custom-validator.php' => array('num1'=>'5','num2'=>'5','submit1'=>'Send',),
	'naming-containers.php' => array('first'=>array('name'=>'James Bond','email'=>'bond@007.com','street'=>'Unknown','city'=>'London','country'=>'GB',),'second'=>array('name'=>'Jim Beam','email'=>'jim@beam.com','street'=>'','city'=>'','country'=>'US',),'submit1'=>'Send',),
	'custom-encoding.php' => array('name'=>'Žlu&#357;ou&#269;ký k&#367;&#328;','country'=>array(0=>'&#268;eská republika',1=>'SlovakiaXX',2=>'Japan',),'note'=>'&#1078;&#1077;&#1076;','submit1'=>'Send','userid'=>'k&#367;&#328;',),
	'CSRF-protection.php' => array('text'=>'a','submit1'=>'Send',),
);

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = $_data[basename($_SERVER['argv'][0])];
