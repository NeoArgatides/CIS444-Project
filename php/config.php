<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_WARNING);


date_default_timezone_set('America/Los_Angeles');
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'zlatan2003');
define('DB_NAME', 'team3');


$DBConnect = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($DBConnect->connect_error) {
    die("Connection failed: " . $DBConnect->connect_error);
}
?>
