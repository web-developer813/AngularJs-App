<?php
require_once 'common.php';

function getCount(&$response) {
	global $mysqli, $target, $tablePrefix;

	if ($target == 'user') {
		$query = "SELECT count(*) FROM `{$tablePrefix}user` a ";
	} 
	if ($target == 'appointment') {
        if (isset($_GET['f']) && $_GET['f'] == "penaw") {
            $query = "SELECT count(*) FROM `{$tablePrefix}appointment` a
            LEFT JOIN `{$tablePrefix}user` u2 on a.appointmentSetter = u2.id ";
        } else {
            $query = "SELECT count(*) FROM `{$tablePrefix}appointment` a ";
        }

	}

	$query .= getWhereQueryPart();	

	$result = $mysqli->query($query);

	if(!$result) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
	} else {
		$response['error'] = false;
		$resArr = $result->fetch_array(MYSQLI_NUM);
		$response['total'] = $resArr[0];
	}
}

function getWhereQueryPart() {
	global $target, $userRole, $userID,$mysqli,$tablePrefix;

	$query = " WHERE 1=1 ";

	if ($target == "user") {
		$query .= " AND deleted = 0 AND id > 0 ";
	} 

	if ($target == 'appointment') {

		// Show salesmen only appointments assigned to them.
		if ($userRole == 0) {
			$query .= " AND a.salesman = {$userID} ";
		}else if($userRole == 3 || $userRole == 4 || $userRole == 5){
            $salesman_ids = array();
            $relationLevel = array(3,4,5,0);
            $roles  = join(',', $relationLevel);

            $sql_parents = "select id from `{$tablePrefix}user` where deleted =0 and role IN (".$roles.") ORDER BY id DESC ";

            $result_parents = $mysqli->query($sql_parents);

            if($result_parents) {
                while ($row = $result_parents->fetch_assoc()) {
                    $salesman_ids[] = $row['id'];
                }
            }
            $salesmans = join(',' , $salesman_ids);
            $query .= " AND a.salesman IN ( {$salesmans} )";
        }

		// Requesting search by ID
		if (isset($_GET['s'])) {
			$s = $_GET['s'];

            if (isset($_GET['f']) && ($_GET['f'] == 'penaw' || $_GET['f'] == 'paidacc')) {
                $query .= " AND concat(u2.name, ' ', u2.lastName) LIKE '%{$s}%' ";
            } else {
                $query .= " AND (a.id = '{$s}' OR
				concat(a.name, ' ', a.lastName) LIKE '%{$s}%' OR
				a.address LIKE '%{$s}%' OR
				concat(u.name, ' ', u.lastName) LIKE '%{$s}%' OR
				concat(u2.name, ' ', u2.lastName) LIKE '%{$s}%') ";
            }

		}

        if (isset($_GET['f'])) {

            switch ($_GET['f']) {
                case 'penaw':
                    // Pending awards
                    $query .= " AND (a.installation = 1 OR a.installation = 2 OR a.status IN(2, 5)) AND a.paid = 0 AND u2.hasFee = 1";
                break;

                case 'paidacc':
                    $query .= " AND a.paid = 1 ";
                break;

                default:
                    if (!isset($_GET['s'])) {
                        $query .= " AND a.status = {$_GET['f']} ";
                    }
            }

        }
						

	}

	return $query;
}


function checkGoalBeforeSave($salesman, $startDate, $endDate) {
	global $mysqli, $tablePrefix;

	if ($startDate >= $endDate) {
		$response['error'] = true;
		$response['data'] = "Start date must be before the end date";
		echo json_encode($response);
		exit;		
	}


	$query = "SELECT id FROM `{$tablePrefix}goal` WHERE salesman = '{$salesman}' AND '{$startDate}' < endDate AND '{$endDate}' > startDate";

	$result = $mysqli->query($query);
	if(!$result) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
	} else {
		
		if ($result->num_rows > 0) {
			$response['error'] = true;
			$response['data'] = "Goal date range collapses with another goal's one for selected Salesman";
			
		} else {
			$response['error'] = false;
		}
	}

	if ($response['error']) {
		echo json_encode($response);
		exit;
	}
	
}



function getNumberOfCurrentSalesForGoal($salesmanID, $startDate, $endDate) {
	global $mysqli, $tablePrefix;

	$query = "SELECT id FROM `{$tablePrefix}appointment` WHERE salesman = {$salesmanID} AND (appointmentDate between '{$startDate}' AND '{$endDate}') AND status = 1";
	$result = $mysqli->query($query);
	if(!$result) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
		echo json_encode($response);
		exit;
	} else {
		return $result->num_rows;
	}
}

function countSalesForGoal($salesmanID, $appointmentDate) {
	global $mysqli, $tablePrefix;


	// Find goal
	$query = "SELECT * FROM `{$tablePrefix}goal` WHERE salesman = {$salesmanID} AND '{$appointmentDate}' between startDate AND endDate LIMIT 1";
	$result = $mysqli->query($query);
	if(!$result) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
		echo json_encode($response);
		exit;
	}
	if ($result->num_rows > 0) {
		$goal = $result->fetch_assoc();

		// Find out sales number
		
		$query = "SELECT id FROM `{$tablePrefix}appointment`
		WHERE salesman = {$salesmanID} AND (appointmentDate between '{$goal['startDate']}' AND  '{$goal['endDate']}') AND status = 1";
		$result = $mysqli->query($query);
		if(!$result) {
			$response['error'] = true;
			$response['data'] = $mysqli->error . "\nQuery: {$query}";
			echo json_encode($response);
			exit;
		}	
		
		$numOfSales = $result->num_rows;


		// Updating salesNumber
		if ($numOfSales != $goal['currentSalesNumber']) {
			$goalDoneQueryPart = "";
			$queryU = "";


			// Checking if we need to update goal done and salesman stars number
			if ($numOfSales >= $goal['salesNumber'] && $goal['done'] == 0) {
				$queryU = "UPDATE `{$tablePrefix}user` SET starsNumber = starsNumber + 1 WHERE id = {$salesmanID}";
				$goalDoneQueryPart = " ,done = 1 ";
			}

			if ($numOfSales < $goal['salesNumber'] && $goal['done'] == 1) {
				$queryU = "UPDATE `{$tablePrefix}user` SET starsNumber = starsNumber - 1 WHERE id = {$salesmanID}";
				$goalDoneQueryPart = " ,done = 0 ";
			}	

			// Updating Goal
			$query = "UPDATE `{$tablePrefix}goal` SET currentSalesNumber = {$numOfSales} {$goalDoneQueryPart} WHERE id = {$goal['id']}";
			$result = $mysqli->query($query);
			if(!$result) {
				$response['error'] = true;
				$response['data'] = $mysqli->error . "\nQuery: {$query}";
				echo json_encode($response);
				exit;
			}

			// Updating user

			if ($queryU !== "") {
				$query = $queryU;
				$result = $mysqli->query($query);
				//echo $query;
				if(!$result) {
					$response['error'] = true;
					$response['data'] = $mysqli->error . "\nQuery: {$query}";
					echo json_encode($response);
					exit;
				}
			}	
		}

	}
}




function notifySalesman($action, $confirmationHash = null) {


    global $mysqli, $statuses, $installations;



    $appUrl = 'http://avincii.com/appointments/';
   if (__FILE__ == 'D:\xampp\htdocs\upwork\appointments\connector\functions.php') {
       $appUrl = '/';
   }



	if (isset($_POST['salesman']) && $_POST['salesman'] != "" && $_POST['salesman'] != "null") {

        if ($action == 'edit') {
            $appointmentID = $_POST['id'];
        } else {
            $appointmentID = $mysqli->insert_id;
        }


		$from = "info@avincii.com";

		if (isset($_POST['showPhoneNumber']) && $_POST['showPhoneNumber'] == "true") {
			$showPhoneNumberValue = 1;
		} else {
			$showPhoneNumberValue = 0;
		}

        $appointmentSetterName = getUserName($_POST['appointmentSetter']);

		$message = sprintf("
			Name: %s<br>
			Address: %s<br>
			City: %s<br>
			Zip: %s<br>
			Phone number: %s<br>
			Appointment date: %s<br>
			Appointment setter: %s<br>
			Profile: %s<br>
			Status: %s<br>
			Installation: %s<br><br>"
            ,
			$_POST['name'] . ' ' . $_POST['lastName'],
            displayLocationLink($_POST['address'], $_POST['city']),
			$_POST['city'],
			$_POST['zip'],
			getActualShowPhoneNumber($showPhoneNumberValue, $_POST['status']) ? displayPhoneNumberLink($_POST['phoneNumber']) : 'hidden',
			date('M j Y h:i A', strtotime($_POST['appointmentDate'])),
            $appointmentSetterName,
            implode(', ', decodeProfile($_POST['profile'])),
			$statuses[$_POST['status']],
			$installations[$_POST['installation']]
		);



        $salesmanMessage = $message;
        $superAdminMessage = $message;
        if ($confirmationHash !== null) {

            $salesmanMessage .= sprintf('<a href="%1$s1">accept</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="%1$s0">decline</a><br><br>',
                "{$appUrl}app-confirmation.php?id={$appointmentID}&hash=$confirmationHash&accepted="
            );
        }

        $lastMessagePart = "<a href='{$appUrl}'>{$appUrl}</a>";
        $salesmanMessage .= $lastMessagePart;
        $superAdminMessage .= $lastMessagePart;

        
		$headers = "MIME-Version: 1.0\r\n";
		$headers.= "From: $from\r\n";
		$headers.= "Content-Type: text/html;charset=utf-8\r\n";
		$headers.= "Reply-To: $from\r\n";  
		$headers.= "X-Mailer: PHP/" . phpversion();

		$onAdd = $action == 'add';
		$onEdit = $action == 'edit' && $_POST['status'] == 0;

		if ($onAdd || $onEdit) {
            $salesmanName = getUserName($_POST['salesman']);

			if ($onAdd) {
				$subject = "A new appointment was created for you";
                $subjectSuperAdmin = "A new appointment was created for $salesmanName";
			}

			if ($onEdit) {
				$subject = "Your appointment #{$_POST['id']} has changed its status to Pending";
                $subjectSuperAdmin = "Appointment #{$_POST['id']} for $salesmanName has changed its status to Pending";
			}


            $salesmanEmail = getUserEmail($_POST['salesman']);
            $superAdminEmail = getUserEmail(0);

			myMail($salesmanEmail, $subject, $salesmanMessage, $headers);
            myMail($superAdminEmail, $subjectSuperAdmin, $superAdminMessage, $headers);


		}
	
	}
}





function notifyOfNewComment() {
	global $userID, $tablePrefix;

	$participantEmails = array();
	$appointmentID = $_POST['appointmentID'];

	$query = "SELECT p.author as id, u.email FROM
	(SELECT DISTINCT `author` FROM `{$tablePrefix}comment` WHERE `appointmentID` = {$appointmentID} UNION 
	(select salesman FROM `{$tablePrefix}appointment` WHERE id = {$appointmentID}) UNION
	(select appointmentSetter FROM `{$tablePrefix}appointment` WHERE id = {$appointmentID})) p
	JOIN `{$tablePrefix}user` u on p.author = u.id";

	$result = executeQuery($query);
	while ($author = $result->fetch_assoc()) {

		if ($author['id'] != $userID) { // Excluding current user
			$participantEmails[] = $author['email'];
		}	
	}

	$query = "SELECT * FROM `{$tablePrefix}appointment` WHERE id = {$appointmentID}";
	$result = executeQuery($query);
	$appointment = $result->fetch_assoc();

	$subject = "There is a new comment on appointment #" . $appointmentID;
	$message = sprintf("
		%s wrote:<br><br>
		%s<br><br>
		
		Account No. #%s %s %s %s %s %s<br><br>

		<a href='%s'>%s</a>", 
		getUserName(),
		$_POST['text'],

		$appointmentID,
		$appointment['name'],
		$appointment['lastName'],
        displayLocationLink($appointment['address'], $appointment['city']),
		$appointment['city'],
		$appointment['zip'],

		'http://avincii.com/appointments/',
		'http://avincii.com/appointments/'
	);

	$from = "info@avincii.com";
	$headers = "MIME-Version: 1.0\r\n";
	$headers.= "From: $from\r\n";
	$headers.= "Content-Type: text/html;charset=utf-8\r\n";
	$headers.= "Reply-To: $from\r\n";  
	$headers.= "X-Mailer: PHP/" . phpversion();

	foreach($participantEmails as $participantEmail) {
		$to = $participantEmail;

		myMail($to, $subject, $message, $headers);
	}
}

function executeQuery($query) {
	global $mysqli;

	$result = $mysqli->query($query);

	if(!$result) {
		$response = array();

		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";

		die(json_encode($response));		
	}

	return $result;
}	


function populatingChartData($data, $type) {

	if ($type == 'week') {
		$initialData = array(
			'Monday' => 0,
			'Tuesday' => 0,
			'Wednesday' => 0,
			'Thursday' => 0,
			'Friday' => 0,
			'Saturday' => 0,
			'Sunday' => 0,
		);

		$currentDate = date('l');
	}

	if ($type == 'year') {
		$initialData = array(
			'January' => 0,
			'February' => 0,
			'March' => 0,
			'April' => 0,
			'May' => 0,
			'June' => 0,
			'July' => 0,
			'August' => 0,
			'September' => 0,
			'October' => 0,
			'November' => 0,
			'December' => 0,						
		);		

		$currentDate = date('F');
	}

	if ($type == 'month') {
		for ($i = 1; $i <= 31; $i++) {
			$initialData[$i] = 0;
		}

		$currentDate = date('j');
	}

	// truncating
	
	$offset = array_search($currentDate, array_keys($initialData)) + 1;
	$output = array_slice($initialData, 0, $offset, true);


	foreach ($data as $val) {
		$output[$val[0]] = $val[1];
	}

	$lastOutput = array();
	foreach ($output as $key => $val) {
		$lastOutput[] = array((string)$key, $val);
	}

	return $lastOutput;

}

function getChartOwnerWhereClause($userID) {
	$ownerWhereClause = "";

	if (userIsSalesman()) {
		$ownerWhereClause = " AND salesman = {$userID} ";
	} else {
		if (isset($_GET['user'])) {
			$ownerWhereClause = " AND salesman = {$_GET['user']} ";
		}
	}

	return $ownerWhereClause;
}

function getChartOutputDataForChart($chartDataQuery, $chartType) {
	$result = executeQuery($chartDataQuery);
	$output = array();

	while ($row = $result->fetch_array()) {
		$output[] = array($row[0], (int)$row[1]);
	}

	$output = populatingChartData($output, $chartType);

	return $output;
}

function getChartPerformanceOutputData($performanceQuery) {
	$result = executeQuery($performanceQuery);

	$output = array();
	while ($row = $result->fetch_array()) {
		if ($row[0] == 1)
			$output[] = array('Sale', (int)$row[1]);
		if ($row[0] == 2)
			$output[] = array('No sale', (int)$row[1]);			
	}

	return $output;

}

// getEventsAmountAtTimeRangeQuery('week', 'sales')
function getEventsAmountAtTimeRangeQuery($time, $data) {
	global $tablePrefix, $ownerWhereClause, $timeRangeQueryWhereParts;

	$query = "SELECT count(id) FROM `{$tablePrefix}appointment` WHERE 1 = 1 ";

	switch ($data) {
		case 'appointments':
			$query .= "";
		break;

		case 'demos':
			$query .= " AND status IN (1, 2) ";
		break;

		case 'nodemos':
			$query .= " AND status = 4 ";
		break;

		case 'sales':
			$query .= " AND status = 1 ";
		break;

		case 'installations':
			$query .= " AND installation = 1 ";
		break;

		default:
			die("Wrong data parameter ({$data}) value in getEventsAmountAtTimeRangeQuery function.");
	}

	$query .= " $ownerWhereClause {$timeRangeQueryWhereParts[$time]} ";

	return $query;
}

function getChartGlobalViewData($globalViewQeuries) {
	$output = array();

	foreach ($globalViewQeuries as $field => $globalViewQeury) {
		$result = executeQuery($globalViewQeury);
		$row = $result->fetch_array();
		$output[$field] = $row[0];
	}

	return $output;
}

function checkUserEmail($email) {
	global $tablePrefix;

	$query = "SELECT count(id) FROM `{$tablePrefix}user` WHERE email = '$email' AND deleted = 0";
	$result = executeQuery($query);
	$row = $result->fetch_array();

	if ($row[0] > 0) {
		$output['error'] = true;
		$output['data'] = "A user with this email address already exists.";

		die(json_encode($output));
	}
}