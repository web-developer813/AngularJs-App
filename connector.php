<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'common.php';
require_once 'connector/functions.php';
require_once 'configs/config.php';

session_start();

if(isset($_SESSION['appointments.user'])) {

	$userRole = getUserRole();	
	$userID = getUserID();	

	$action = isset($_GET['a']) ? $_GET['a'] : '';
	$target = isset($_GET['t']) ? $_GET['t'] : '';

	if (!actionIsAllowedForTarget($action, $target)) {
		die("Requested action ({$action}, {$target}) is not allowed for user role {$userRole}");
	}


	$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

	foreach($_POST as $key => $value) {
		$_POST[$key] = $mysqli->real_escape_string($value);
	}
	foreach($_GET as $key => $value) {
		$_GET[$key] = $mysqli->real_escape_string($value);
	}



	require_once 'connector/get.php';
	require_once 'connector/add.php';
	require_once 'connector/edit.php';
	require_once 'connector/delete.php';
	require_once 'connector/other-actions.php';



	$mysqli->close();	
}

