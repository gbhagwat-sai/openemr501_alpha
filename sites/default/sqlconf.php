<?php
//  OpenEMR
//  MySQL Config
//  Referenced from /library/sqlconf.php.

global $disable_utf8_flag;
$disable_utf8_flag = false;

$host	= '10.0.0.4';
$port	= '3306';
$login	= 'billehrdba';
$pass	= '5Qld8#inTc94';
//$dbase	= 'openemr5010';
$dbase	= 'openemr_pss_alpha2';


$sqlconf = array();
global $sqlconf;
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;

//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 1; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
