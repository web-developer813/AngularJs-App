<?php

	if ($action == 'edit') {
		if ($target == 'appointment') {
			$response = array();

			$salesmanValue = isset($_POST['salesman']) && $_POST['salesman'] != "" ? $_POST['salesman'] : "NULL";

			if (isset($_POST['showPhoneNumber']) && $_POST['showPhoneNumber'] == "true") {
				$showPhoneNumberValue = 1;
			} else {
				$showPhoneNumberValue = 0;
			}

			$updateColumns[SALESMAN_ROLE_ID] = array(
				'status',
				'appointmentDate',
			);

			$updateColumns[APPOINTMENT_SETTER_ROLE_ID] = $updateColumns[REGIONAL_MANAGER_ROLE_ID] = $updateColumns[DISTRICT_MANAGER_ROLE_ID] = $updateColumns[SALES_MANAGER_ROLE_ID] = array(
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
			);

			$updateColumns[ADMIN_ROLE_ID] = array(
				'installation',
                'appointmentSetter',
			);

			$updateColumns[SUPERADMIN_ROLE_ID] = array_merge($updateColumns[APPOINTMENT_SETTER_ROLE_ID], $updateColumns[ADMIN_ROLE_ID]);
			

			$updateData = $_POST;
			$updateData['showPhoneNumber'] = $showPhoneNumberValue;
			$updateData['salesman'] = $salesmanValue;
            if (userIsAdmin() || userIsSuperadmin()) {
                $insertData['appointmentSetter'] = $_POST['appointmentSetter'];
            }

			$currUserData = array_map(function($col) use(&$updateData) {
				if (in_array($col, array('profile', 'salesman', 'status', 'installation')))
					return $updateData[$col];
				return sprintf("'%s'", $updateData[$col]);
			}, $updateColumns[getUserRole()]);


            $currUserColumns = $updateColumns[getUserRole()];

            if (isset($_POST['newSalesman']) || isset($_POST['newDate'])) {

                // New salesman assigned to this appointment
                $confirmationHash = md5(time() . 'hello coconut');
                $currUserColumns[] = 'confirmationHash';
                $currUserData[] = "'$confirmationHash'";

                $currUserColumns[] = 'accepted';
                $currUserData[] = "NULL";
            }
		
			$updateStrings = array();
			$i = 0;
			foreach ($currUserColumns as $updateColumn) {
				$updateStrings[] = "`$updateColumn` = " . $currUserData[$i];
				$i++;
			}
			$updateString = implode(', ', $updateStrings);

			$query = sprintf("UPDATE `{$tablePrefix}appointment` SET %s WHERE id = {$_POST['id']} %s",
				$updateString,
				userIsSalesman() ? " AND salesman = {$userID} " : ''
			);

/*			if (userIsSalesman()) {
				$query = "UPDATE `{$tablePrefix}appointment` SET `status` = {$_POST['status']}, `appointmentDate` = '{$_POST['appointmentDate']}' WHERE id = {$_POST['id']} AND salesman = {$userID}";
			} else {
				
				$query = "UPDATE `{$tablePrefix}appointment`
					SET `name` = '{$_POST['name']}', `lastName` = '{$_POST['lastName']}', `address` = '{$_POST['address']}', `city` = '{$_POST['city']}', 
					`zip` = '{$_POST['zip']}', `phoneNumber` = '{$_POST['phoneNumber']}', `showPhoneNumber` = {$showPhoneNumberValue}, `appointmentDate` = '{$_POST['appointmentDate']}',
					`profile` = {$_POST['profile']}, `salesman` = {$salesmanValue}, `status` = {$_POST['status']}, `installation` = {$_POST['installation']}
					WHERE id = {$_POST['id']}";	
		
			}*/

		}

		if ($target == 'user') {
			$response = array();

			$passwordChangeQuery = "";
			if (isset($_POST['changePass']) && $_POST['changePass'] == "true") {
				$passwordChangeQuery = "`password` = '{$_POST['pass1']}', ";
			}

			if(isset($_POST['level']) && $_POST['level'] == "true"){
			    $level = 1;
			    if(isset($_POST['parent'])){
			        $parent_user_id = $_POST['parent'];
                }else{
                    $parent_user_id = 0;
                }
            }else{
                $level = 0;
                $parent_user_id = 0;
            }

			$query = "UPDATE `{$tablePrefix}user`
			SET {$passwordChangeQuery} `name` = '{$_POST['name']}', `lastName` = '{$_POST['lastName']}', `address` = '{$_POST['address']}', `city` = '{$_POST['city']}', 
				`zip` = '{$_POST['zip']}', `phoneNumber` = '{$_POST['phoneNumber']}', `level` = {$level}, `parent_user_id` = {$parent_user_id}
				WHERE id = {$_POST['id']}";				
		}

		// Common part

		if(!$mysqli->query($query)) {
			$response['error'] = true;
			$response['data'] = $mysqli->error . "\nQuery: {$query}";
		} else {
			$response['error'] = false;


            if ($target == 'appointment') {
                //if (!userIsAdmin()) { //because no need in notification
                    // wee need it here after query to get last insert id inside function
                    if (isset($confirmationHash)) {
                        notifySalesman($action, $confirmationHash);
                    } else {
                        notifySalesman($action);
                    }
               // }

                if ($salesmanValue !== "NULL") {
                    countSalesForGoal($salesmanValue, $_POST['appointmentDate']);
                }
            }

			if ($target == 'user') {
				// Editing fee
				if ($_POST['hasFee'] == "true" && $_POST['role'] == APPOINTMENT_SETTER_ROLE_ID) {
					$query1 = "INSERT INTO `{$tablePrefix}user_fees` (`userID`, `fee`, `date`, `type`) VALUES ({$_POST['id']}, {$_POST['fee1']}, NOW(), 1)";
                    $query2 = "INSERT INTO `{$tablePrefix}user_fees` (`userID`, `fee`, `date`, `type`) VALUES ({$_POST['id']}, {$_POST['fee2']}, NOW(), 2)";

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

		echo json_encode($response);
	}