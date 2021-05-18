<?php
//  OpenEMR
//  MySQL Config
//  Referenced from /library/sqlconf.php.

global $disable_utf8_flag;
$disable_utf8_flag = false;

$host	= '127.0.0.1';
$port	= '3306';
$login	= 'root';
$pass	= '';
$dbase	= 'payehr_fcg';


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
