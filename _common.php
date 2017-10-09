<?php
require 'vendor/autoload.php';
use Sendinblue\Mailin;
require_once 'configs/config.php';

define('SALESMAN_ROLE_ID', 0);
define('APPOINTMENT_SETTER_ROLE_ID', 1);
define('ADMIN_ROLE_ID', 2);
define('SUPERADMIN_ROLE_ID', 9);

function myMail($to, $subject, $message, $headers = null) {
    global $appDomain;
    if ($headers === null) {
        $from = "info@" . $appDomain;

        $headers = "MIME-Version: 1.0\r\n";
        $headers.= "From: $from\r\n";
        $headers.= "Content-Type: text/html;charset=utf-8\r\n";
        $headers.= "Reply-To: $from\r\n";
        $headers.= "X-Mailer: PHP/" . phpversion();
    }
    //if (strpos(__FILE__, 'D:\xampp\htdocs\upwork\appointments') === false ) {
    if (1) {


        $mailin = new Mailin("https://api.sendinblue.com/v2.0","S1a0skbdzD6jHNEX");


        $data = array( "to" => array($to=>""),
            "from" => array("info@avincii.com","Avincii"),
            "subject" => $subject,
            "html" => $message,
            "headers" => array("Content-Type"=> "text/html; charset=iso-8859-1","X-param1"=> "value1", "X-param2"=> "value2","X-Mailin-custom"=>"my custom value", "X-Mailin-IP"=> "102.102.1.2", "X-Mailin-Tag" => "My tag"),
        );

        $mailinResp = $mailin->send_email($data);

        if ($mailinResp["code"] == "success") {
            return true;
        }

        return false;



    }

    return true;
}

$declineReasons = array(
    'Time conflict', 'Previous demo', 'Sickness', 'Weather', 'Other', 'Repeat'
);

function getUserRole() {
	return $_SESSION['appointments.user']['role'];
}

function getUserID() {
	return $_SESSION['appointments.user']['id'];
}

function getUserEmail($id) {
    global $tablePrefix, $mysqli;

    $query = "SELECT email FROM `{$tablePrefix}user` WHERE id = " . $id;
    $result = $mysqli->query($query);

    if(!$result) {
        $response['error'] = true;
        $response['data'] = $mysqli->error . "\nQuery: {$query}";
        echo json_encode($response);
        exit;
    }

    $row = $result->fetch_array();

    return $row[0];
}

function getUserName($id = null) {
    global $tablePrefix, $mysqli;

    if ($id == null) {
        return $_SESSION['appointments.user']['name'] . ' ' . $_SESSION['appointments.user']['lastName'];
    }

    $query = "SELECT concat(name, ' ', lastName) FROM `{$tablePrefix}user` WHERE id = " . $id;
    $result = $mysqli->query($query);

    if(!$result) {
        $response['error'] = true;
        $response['data'] = $mysqli->error . "\nQuery: {$query}";
        echo json_encode($response);
        exit;
    }

    $row = $result->fetch_array();

    return $row[0];
}

function userIsSalesman() {
	return (getUserRole() == SALESMAN_ROLE_ID);
}

function userIsAdmin() {
	return (getUserRole() == ADMIN_ROLE_ID);
}

function userIsSuperadmin() {
	return (getUserRole() == SUPERADMIN_ROLE_ID);
}

function displayPhoneNumberLink($pn) {
    return sprintf('<a href="tel:%s">%s</a>', preg_replace('/[^0-9]/', '', $pn), $pn);
}

function displayLocationLink($locationText, $city) {
    return sprintf('<a href="https://www.google.com.ua/maps/place/%s">%s</a>', urlencode($locationText . ', ' . $city . ', USA'), $locationText);
}

function getActualShowPhoneNumber($showPhoneNumber, $status) {
    return ( $showPhoneNumber || in_array($status, array(1, 2, 4)) );
}

function decodeProfile($profileNum) {
    $profiles = array('NH', 'Kids', 'Bad taste/odor');
    $output = array();

    $profileAsNumber = intval($profileNum);

    for ($i = sizeof($profiles) - 1; $i >= 0; $i--) {
        $testProfile = $profiles[$i];
        if ($profileAsNumber % 2 == 1) {
            $output[] = $testProfile;
        }
        $profileAsNumber = $profileAsNumber >> 1;
    }

    $output = array_reverse($output);

    return $output;
}

$statuses = array('Pending', 'Sale', 'No Sale', 'Re-Schedule', 'No demo', 'Not approved');
$installations = array('Pending', 'Installed', 'Not installed');

function actionIsAllowedForTarget($requestedAction, $reqestedTarget) {
	$userRole = getUserRole();

	$userRoleAllowedActions = array(
		SALESMAN_ROLE_ID 			=> array(
			'appointment' => array('get', 'edit'),
			'chart' => array('get'),
			'comment' => array('get', 'add'),
			'goal' => array('get'),
		),

		APPOINTMENT_SETTER_ROLE_ID 	=> array(
			'appointment' => array('get', 'add', 'edit', 'delete'),
			'tinyUser' => array('get'),	
			'chart' => array('get'),
			'comment' => array('get', 'add'),
			'user' => array('get'),
			'goal' => array('get', 'add', 'delete', 'check'),
		),

		ADMIN_ROLE_ID 				=> array(
			'appointment' => array('get', 'edit'),
			'tinyUser' => array('get'),	
			'chart' => array('get'),
			'user' => array('get'),
			'comment' => array('get', 'add'),
		),

		SUPERADMIN_ROLE_ID 			=> array(
			'appointment' => array('get', 'add', 'edit', 'delete', 'pay', 'invoiceSeen'),
			'tinyUser' => array('get', 'resetStars'),			
			'user' => array('get', 'add', 'edit', 'delete'),
			'chart' => array('get'),
			'comment' => array('get', 'add'),
			'goal' => array('get', 'add', 'delete', 'check'),
			'payment' => array('get'),
			'invoice' => array('get'),
		), 
	);

	

	$allowedActions = isset($userRoleAllowedActions[$userRole][$reqestedTarget]) ? $userRoleAllowedActions[$userRole][$reqestedTarget] : array();

	return in_array($requestedAction, $allowedActions);
}