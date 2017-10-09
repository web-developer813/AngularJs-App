<?php
	if ($action == 'get' && $target != 'chart') {
		if ($target == 'appointment') {
			$response = array();

			$entriesPerPage = $_GET['n'];
			$showPage = $_GET['p'];
			$offset = ($showPage - 1) * $entriesPerPage;		
			$query = "SELECT a.*, 
				concat(u.name, ' ', u.lastName) as salesmanName,
				concat(u2.name, ' ', u2.lastName) as appointmentSetterName
				FROM `{$tablePrefix}appointment` a LEFT JOIN `{$tablePrefix}user` u ON a.salesman = u.id LEFT JOIN `{$tablePrefix}user` u2 on a.appointmentSetter = u2.id ";		


			$query .= getWhereQueryPart();
			$query .= " ORDER BY {$_GET['orderBy']} " . (isset($_GET['orderDesc']) ? "DESC" : "") . " LIMIT {$offset}, {$entriesPerPage} ";
		}

		if ($target ==  'user') {
			$response = array();
			
			$query = sprintf(
				"SELECT id, email, role, name, lastName, address, city, zip, phoneNumber,level,parent_user_id, hasFee %s FROM `{$tablePrefix}user` a %s ",
				(userIsAdmin() || userIsSuperadmin()) ? ", fee, ufm.type as feeType " : "",
				(userIsAdmin() || userIsSuperadmin()) ? "
				LEFT JOIN (select uf.`date`, uf.userID, uf.fee, uf.`type` from
                (SELECT userID, max(`date`) as `date`, `type` FROM `{$tablePrefix}user_fees` group by userID, `type`) md
                join `{$tablePrefix}user_fees` uf
                on md.`date` = uf.`date` AND uf.userID = md.userID AND md.`type` = uf.`type`) ufm
                ON a.id = ufm.userID  " : ""
			);
			//$query = "SELECT id, email, role, fee, name, lastName, address, city, zip, phoneNumber FROM `{$tablePrefix}user` a ";
			$query .= getWhereQueryPart();
			$query .= "ORDER BY id DESC ";
            if (userIsAdmin() || userIsSuperadmin()) {
                $query .= " , feeType ";
            }

		}	


		if ($target == 'tinyUser') {
			$response = array();

			$query = "SELECT id, name, lastName, concat(name, ' ', lastName) as fullName , role, starsNumber FROM `{$tablePrefix}user` a WHERE deleted = 0 AND role IN (0, 1) ORDER BY id DESC ";
		}


		if ($target == 'comment') {
			$response = array();

			$query = "SELECT `text`, `author`, `date`, concat(u.name, ' ', u.lastName) as authorName
			FROM `{$tablePrefix}comment` c LEFT JOIN `{$tablePrefix}user` u ON c.author = u.id
			WHERE `appointmentID` = {$_POST['appointmentID']} ORDER BY `date` DESC ";
		}

		if ($target == 'goal') {
			$response = array();

/*			$query = "SELECT  g.*, count(a.id) as currentSalesNumber, concat(u.name, ' ', u.lastName) as salesmanName, u.starsNumber as salesmanStars
			FROM goal g left join (SELECT * FROM `appointment` WHERE status = 1) a 
			ON (a.salesman = g.salesman AND `appointmentDate` between startDate AND endDate) LEFT JOIN `user` u ON g.salesman = u.id
			GROUP BY g.id ORDER BY g.id DESC";*/

			$queryWherePart = "";
			$salesmanCurrentSelectField = "";
			if ($userRole == 0) {
				$queryWherePart = " WHERE salesman = {$userID} AND startDate < NOW() "; 
				$salesmanCurrentSelectField = " , (startDate < NOW() AND NOW() < endDate) as current ";
			} else {
				$filterValue = $_GET['f'];
				switch($filterValue) {
					case 0:
						$queryWherePart = " WHERE startDate < NOW() AND NOW() < endDate ";
					break;
					case 1:
						$queryWherePart = " WHERE NOW() > endDate ";
					break;
					case 2:
						$queryWherePart = " WHERE startDate > NOW() ";
					break;
				}
			}

			$query = "SELECT g.*, concat(u.name, ' ', u.lastName) as salesmanName {$salesmanCurrentSelectField}
			FROM `{$tablePrefix}goal` g LEFT JOIN `{$tablePrefix}user` u ON g.salesman = u.id ";
			$query .= $queryWherePart;
			$query .= " ORDER BY g.startDate DESC ";			
		}

		// Common part for all targets

		$result = $mysqli->query($query);
		if(!$result) {
			$response['error'] = true;
			$response['data'] = $mysqli->error . "\nQuery: {$query}";
		} else {
			$response['error'] = false;
			$response['data'] = array();
			while ($row = $result->fetch_assoc()) {
				if ($target == 'appointment') {
					$actualShowPhoneNumber = getActualShowPhoneNumber($row['showPhoneNumber'], $row['status']);
					$row['actualShowPhoneNumber'] = $actualShowPhoneNumber;
					if(userIsSalesman() && !$actualShowPhoneNumber) {
						$row['phoneNumber'] = "";
					}
				}
				$response['data'][] = $row;
			}

            if ($target == 'user') {
                // setting fees from rows to columns
                if (sizeof($response['data']) > 1) {


                    for ($i = 1; $i < sizeof($response['data']); $i++) {
                        if ($response['data'][$i]['id'] == $response['data'][$i - 1]['id']) {
                            $response['data'][$i - 1]['fee1'] = $response['data'][$i - 1]['fee'];
                            $response['data'][$i - 1]['fee2'] = $response['data'][$i]['fee'];
                            unset($response['data'][$i]);
                            $response['data'] = array_values($response['data']);
                            $i--;
                        }
                    }
                }


            }
		}

		if (in_array($target, array('appointment'))) {
			if (!$response['error']) {

				if (isset($_GET['s'])) {
					$response['total'] = 1;
				} else {
					getCount($response);			
				}
			}			
		}


		echo json_encode($response);
		

	}

	if ($action == 'get' && $target == 'chart') {

		$chartTime = $_GET['time'];

		$ownerWhereClause = getChartOwnerWhereClause($userID);

		$timeRangeQueryWhereParts = array(
			'week' 	=> " AND appointmentDate > FROM_DAYS(TO_DAYS(CURDATE()) -MOD(TO_DAYS(CURDATE()) -2, 7)) ",
			'month' => " AND YEAR(appointmentDate) = YEAR(NOW()) AND MONTH(appointmentDate) = MONTH(NOW()) ",
			'year' 	=> " AND YEAR(appointmentDate) = YEAR(NOW()) ",
		);

		if (isset($timeRangeQueryWhereParts[$chartTime])) {
			$timeRangeQueryWherePart = $timeRangeQueryWhereParts[$chartTime];
		} else {
			die("Couldn't find time range query where part for type {$chartTime}");
		}

		switch ($chartTime) {
			case 'week':
				$chartDataQuery = "SELECT DAYNAME(appointmentDate) as dayName, COUNT(`id`) as amount
					FROM `{$tablePrefix}appointment` 
					WHERE status = 1 {$ownerWhereClause} {$timeRangeQueryWherePart}
					GROUP BY dayName
					ORDER BY appointmentDate";
			break;

			case 'month':
				$chartDataQuery = "SELECT DAY(appointmentDate) as `day`, COUNT(`id`) as amount
					FROM `{$tablePrefix}appointment` 
					WHERE status = 1 {$ownerWhereClause} {$timeRangeQueryWherePart}
					GROUP BY `day`
					ORDER BY appointmentDate";
			break;

			case 'year':
				$chartDataQuery = "SELECT MONTHNAME(appointmentDate) as `monthName`, COUNT(`id`) as amount
					FROM `{$tablePrefix}appointment` 
					WHERE status = 1 {$ownerWhereClause} {$timeRangeQueryWherePart}
					GROUP BY `monthName`
					ORDER BY appointmentDate";
			break;

			default:
				die('Wrong chart type');
		}

		$performanceQuery = "SELECT statuses.*, count(a.status) 
			FROM (SELECT 1 as status UNION SELECT 2) statuses 
			LEFT JOIN (SELECT * FROM `{$tablePrefix}appointment` WHERE 1 = 1 {$ownerWhereClause} {$timeRangeQueryWherePart}) a 
			ON statuses.status = a.status
			GROUP BY statuses.status";


		$dataForGlobalView = array('appointments', 'demos', 'nodemos', 'sales', 'installations');
		$globalViewQeuries = array();
		foreach ($dataForGlobalView as $data) {
			$globalViewQeuries[$data] = getEventsAmountAtTimeRangeQuery($chartTime, $data);
		}


		$response['error'] = false;
		$response['data']['chart'] = getChartOutputDataForChart($chartDataQuery, $chartTime);
		$response['data']['performance'] = getChartPerformanceOutputData($performanceQuery);
		$response['data']['globalview'] = getChartGlobalViewData($globalViewQeuries);

		echo json_encode($response);
	}