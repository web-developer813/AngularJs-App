<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'common.php';
require_once 'configs/config.php';

define('VIEWS_PATH', 'views/');


function detectSuperAdminUser() {

	if (loggedIntoWP()) {
    	return array('id' => 0, 'role' => 9, 'name' => 'Superadmin', 'lastName' => '');
	}

	return false;
}

function loggedIntoWP() {

	// Fallback for localhost
	if ('D:\xampp\htdocs\upwork\appointments\index.php' == __FILE__) {
		return 0;
	}
	include_once('../wp-load.php');

	$current_user = wp_get_current_user();

	return (1 == $current_user->ID);
}

function testSuperAdminLogin() {
	if (getUserRole() == 9 && !loggedIntoWP()) {
		logout();
	}
}

function logout() {
	unset($_SESSION['appointments.user']);
	session_destroy();	
}





function displayUserStars() {
	if (userIsSalesman()) {
		echo '<salesman-stars amount="' . $_SESSION['appointments.user']['starsNumber'] . '"></salesman-stars>';
	}

	echo '';
}

function detectRegularUser() {
	global $tablePrefix, $mysqli, $dbHost, $dbUser, $dbPass, $dbName;
	
	if (sizeof($_POST)) { 


		$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

		$email = $_POST['email'];
		$password = $_POST['password'];

		$user = checkUserCredentials($mysqli, $email, $password);

		$mysqli->close();		

		return $user;	
	}

	return false;
}

function checkUserCredentials($mysqli, $email, $password) {
	global $tablePrefix;

	$query = sprintf("SELECT `id`, `role`, `name`, `lastName`, `starsNumber` FROM `{$tablePrefix}user` WHERE `email` = '%s' AND `password` = '%s' AND deleted = 0 ",
		$mysqli->real_escape_string($email),
		$mysqli->real_escape_string($password)
	);



	$result = $mysqli->query($query);

	if ($result->num_rows >= 1) {
		$user = $result->fetch_assoc();
		return $user;
	}
	
	return false;
}

function showView() {
	if(isset($_SESSION['appointments.user'])) {
		include VIEWS_PATH . 'admin.html';
	} else {
		include VIEWS_PATH . 'signin.html';
	}	
}



session_start();

if (!isset($_SESSION['appointments.user'])) {
	

	//$user = detectSuperAdminUser();

	//if ($user === false) {
		$user = detectRegularUser();

	//}

	if ($user !== false) {
		$_SESSION['appointments.user'] = $user;
		header("Location: " . $_SERVER['REQUEST_URI']);
	}

} else {
	// Checking for logout

	//testSuperAdminLogin();

	if (isset($_POST['logout'])) {
		logout();
	}
}


showView();

