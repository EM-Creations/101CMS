<?php

define("DB_HOST", "localhost");

define("DB_USER", "root");

define("DB_PASSWORD", "");

define("DB_NAME", "101cms");

define("DB_CONN", "mysqli");

$_mysql = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($_mysql->connect_errno) { // If there was a problem connecting to the database
	die("Error connecting to database: (" . $_mysql->connect_errno . ") " . $_mysql->connect_error);
}
?>