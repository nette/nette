<?php

$_data = array(
	'example1.php' => array('name'=>'John Doe ','age'=>'  12 ','email'=>'@','street'=>'','city'=>'','country'=>'CZ','password'=>'xxx','password2'=>'xxx','note'=>'','userid'=>'231','submit1'=>'Send',),
	'example2.php' => array('name'=>'John Doe ','age'=>'','email'=>'  @ ','send'=>'on','street'=>'','city'=>'','country'=>'HU','password'=>'xxx','password2'=>'','note'=>'','submit1'=>'Send','userid'=>'231',),
	'example3.php' => array('name'=>'John Doe ','age'=>'90','gender'=>'f','email'=>'david@grudl.com','street'=>'','city'=>'','country'=>'CZ','password'=>'123','password2'=>'123','note'=>'Hello"world"\\x','submit1'=>'Odeslat','userid'=>'231',),
	'example4.php' => array('name'=>'John Doe ','age'=>'9.9','email'=>'@','street'=>'','city'=>'Troubsko','country'=>'0','password'=>'xx','password2'=>'xx','note'=>'','submit1'=>'Send','userid'=>'231',),
	'example5.php' => array('num1'=>'5','num2'=>'5','submit1'=>'Send',),
	'example6.php' => array('first'=>array('name'=>'James Bond','email'=>'bond@007.com','street'=>'Unknown','city'=>'London','country'=>'GB',),'second'=>array('name'=>'Jim Beam','email'=>'jim@beam.com','street'=>'','city'=>'','country'=>'US',),'submit1'=>'Send',),
	'example7.php' => array('name'=>'Žlu&#357;ou&#269;ký k&#367;&#328;','country'=>array(0=>'&#268;eská republika',1=>'SlovakiaXX',2=>'Japan',),'note'=>'&#1078;&#1077;&#1076;','submit1'=>'Send','userid'=>'k&#367;&#328;',),
);

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = $_data[basename($_SERVER['argv'][0])];
