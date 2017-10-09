<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 6/28/2016
 * Time: 11:05 PM
 */


require_once '../common.php';
require_once '../configs/config.php';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);



$query = "SELECT a.*, u.email FROM `{$tablePrefix}appointment` a JOIN `{$tablePrefix}user` u ON a.salesman = u.id
WHERE
a.appointmentDate >= DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00') - INTERVAL 1 DAY + INTERVAL 2 HOUR
AND
a.appointmentDate < DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00') - INTERVAL 1 DAY + INTERVAL 3 HOUR";
$result = $mysqli->query($query);

if(!$result) {
   // Main admin that error occured
    $superAdminEmail = getUserEmail(0);
    myMail($superAdminEmail, 'Error in salesman change status notification', 'Error in salesman change status notification. Please contact web-master');

} else {
    $subject = "Please update appointment status";

    printf('Emails to notify found: %s<br>', $result->num_rows);

    while ($row = $result->fetch_array()) {

        if ($row['email']) {

            $message = sprintf('
            ID: %s<br>
			Name: %s<br>
			Address: %s<br>
			City: %s<br>
			Zip: %s<br>
			Phone number: %s<br>
			Appointment date: %s<br>
			Appointment setter: %s<br>
			Profile: %s<br>
			Status: %s<br>
			Installation: %s<br><br>

			<a href="%schangeStatus/salesmanChangeStatus.php?csh=%s">Change status</a>'
                ,
                $row['id'],
                $row['name'] . ' ' . $row['lastName'],
                displayLocationLink($row['address'], $row['city']),
                $row['city'],
                $row['zip'],
                getActualShowPhoneNumber($row['showPhoneNumber'], $row['status']) ? displayPhoneNumberLink($row['phoneNumber']) : 'hidden',
                date('M j Y h:i A', strtotime($row['appointmentDate'])),
                getUserName($row['appointmentSetter']),
                implode(', ', decodeProfile($row['profile'])),
                $statuses[$row['status']],
                $installations[$row['installation']],
                $appUrl,
                $row['csh']
            );

            echo $row['email'];
            if (myMail($row['email'], $subject, $message)) {
                echo ' was successfully notified<br>';
            } else {
                echo ' error with sending email<br>';
            }
        }
    }

}