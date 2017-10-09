<?php
	if ($action == 'add') {
		if ($target == 'appointment') {		
			$response = array();

			$salesmanValue = isset($_POST['salesman']) && $_POST['salesman'] != "" ? $_POST['salesman'] : "NULL";

			if (isset($_POST['showPhoneNumber']) && $_POST['showPhoneNumber'] == "true") {
				$showPhoneNumberValue = 1;
			} else {
				$showPhoneNumberValue = 0;
			}


			$insertColumns[APPOINTMENT_SETTER_ROLE_ID] = $insertColumns[REGIONAL_MANAGER_ROLE_ID] = $insertColumns[DISTRICT_MANAGER_ROLE_ID] = $insertColumns[SALES_MANAGER_ROLE_ID] =  array(
				'creationDate',
				'name' ,
				'lastName',
				'address',
				'city',
				'zip',
				'phoneNumber',
				'showPhoneNumber',
				'appointmentDate',
				'profile',
				'salesman',
				'status',
				'appointmentSetter',
				'commentsNumber'
			);

			$insertColumns[ADMIN_ROLE_ID] = array(
				'installation',
			);

			$insertColumns[SUPERADMIN_ROLE_ID] = array_merge($insertColumns[APPOINTMENT_SETTER_ROLE_ID], $insertColumns[ADMIN_ROLE_ID]);

			$insertData = $_POST;
			$insertData['showPhoneNumber'] = $showPhoneNumberValue;
			$insertData['salesman'] = $salesmanValue;
            if (userIsAdmin() || userIsSuperadmin()) {
                $insertData['appointmentSetter'] = $_POST['appointmentSetter'];
            } else {
                $insertData['appointmentSetter'] = $userID;
            }

			$insertData['commentsNumber'] = 0;


			$currUserColumns = array_map(function($col) {return "`$col`";}, $insertColumns[getUserRole()]);


			$currUserData = array_map(function($col) use(&$insertData) {
				if (in_array($col, array('profile', 'salesman', 'status', 'installation', 'appointmentSetter', 'commentsNumber')))
					return $insertData[$col];
				return sprintf("'%s'", $insertData[$col]);
			}, $insertColumns[getUserRole()]);


            // Common for all
            // Approve\Decline hash
            $confirmationHash = md5(time() . 'hello coconut');
            $currUserColumns[] = 'confirmationHash';
            $currUserData[] = "'$confirmationHash'";
            // Change status hash
            $csh = md5(time() . 'hello coconut007');
            $currUserColumns[] = 'csh';
            $currUserData[] = "'$csh'";
			$query = sprintf("INSERT INTO `{$tablePrefix}appointment` (%s) VALUES (%s)", 
				implode(', ', $currUserColumns),
				implode(', ', $currUserData)
			);

/*			$query = "INSERT INTO `{$tablePrefix}appointment`
			(`creationDate`, `name`, `lastName`, `address`, `city`, `zip`, `phoneNumber`, `showPhoneNumber`, `appointmentDate`, `profile`, `salesman`, `status`, `installation`, `appointmentSetter`, `commentsNumber`)
			VALUES ('{$_POST['creationDate']}', '{$_POST['name']}', '{$_POST['lastName']}', '{$_POST['address']}', '{$_POST['city']}',
				'{$_POST['zip']}', '{$_POST['phoneNumber']}', '{$showPhoneNumberValue}', '{$_POST['appointmentDate']}', {$_POST['profile']}, {$salesmanValue}, {$_POST['status']}, 
				{$_POST['installation']}, {$userID}, 0)";*/

						
		}

		if ($target == 'user') {
			$response = array();

			checkUserEmail($_POST['email']);

			$hasFee = isset($_POST['hasFee']) && $_POST['hasFee'] == "true" ? true : false;
			$hasFeeNum = $hasFee ? 1 : 0;

			$level = isset($_POST['level'])  && $_POST['level'] =="true" ? true : false;
			$levelNum = $level ? 1 : 0 ;

			if(isset($_POST['parent'])){
                $parent_user_id = $_POST['parent'];
            }else{
                $parent_user_id = 0;
            }


			$query = "INSERT INTO `{$tablePrefix}user`
				(`email`, `password`, `role`, `name`, `lastName`, `address`, `city`, `zip`, `phoneNumber`, `hasFee`,`level`, `parent_user_id`)
				VALUES ('{$_POST['email']}', '{$_POST['pass1']}', {$_POST['role']}, '{$_POST['name']}', '{$_POST['lastName']}', '{$_POST['address']}', '{$_POST['city']}',
					'{$_POST['zip']}', '{$_POST['phoneNumber']}', {$hasFeeNum},{$levelNum},{$parent_user_id})";


		}	


		if ($target == 'comment') {
			$response = array();

			$query = "INSERT INTO `{$tablePrefix}comment`
				(`author`, `date`, `text`, `appointmentID`)
				VALUES ({$userID}, '{$_POST['date']}', '{$_POST['text']}', {$_POST['appointmentID']})";


	
		}	

		if ($target == 'goal') {
			$response = array();

			checkGoalBeforeSave($_POST['salesman'], $_POST['startDate'], $_POST['endDate']);

			$currentSalesNumber = getNumberOfCurrentSalesForGoal($_POST['salesman'], $_POST['startDate'], $_POST['endDate']);
			$query = "INSERT INTO `{$tablePrefix}goal`
				(`title`, `salesman`, `startDate`, `endDate`, `salesNumber`, `currentSalesNumber`)
				VALUES ('{$_POST['title']}', {$_POST['salesman']}, '{$_POST['startDate']}', '{$_POST['endDate']}', {$_POST['salesNumber']}, {$currentSalesNumber})";		
		}

		// Common part

		$response['error'] = false;

		if(!$mysqli->query($query)) {
			$response['error'] = true;
			$response['data'] = $mysqli->error . "\nQuery: {$query}";
		} else {
			$response['error'] = false;

            if ($target == 'appointment') {
                if (!userIsAdmin()) { //because no need in notification
                    notifySalesman($action, $confirmationHash); // wee need it here after query to get last insert id inside function
                }
            }

			if ($target == 'user') {
				// Adding fee


				if ($hasFee) {
					$query1 = "INSERT INTO `{$tablePrefix}user_fees` (`userID`, `fee`, `date`, `type`) VALUES (" . $mysqli->insert_id . ", {$_POST['fee1']}, NOW(), 1)";
                    $query2 = "INSERT INTO `{$tablePrefix}user_fees` (`userID`, `fee`, `date`, `type`) VALUES (" . $mysqli->insert_id . ", {$_POST['fee2']}, NOW(), 2)";
					if(!$mysqli->query($query1)) {
						$response['error'] = true;
						$response['data'] = $mysqli->error . "\nQuery: {$query1}";

						die(json_encode($response));
					} else {
						$response['error'] = false;
					}

                    if(!$mysqli->query($query2)) {
                        $response['error'] = true;
                        $response['data'] = $mysqli->error . "\nQuery: {$query2}";

                        die(json_encode($response));
                    } else {
                        $response['error'] = false;
                    }
                }

			}
		}			
		

		if ($target == 'comment' && !$response['error']) {
			$query = "UPDATE `{$tablePrefix}appointment` SET `commentsNumber` = `commentsNumber` + 1 WHERE id = {$_POST['appointmentID']}";
			if(!$mysqli->query($query)) {
				$response['error'] = true;
				$response['data'] = $mysqli->error . "\nQuery: {$query}";
			}		

			notifyOfNewComment();		
		}


		echo json_encode($response);
	}
