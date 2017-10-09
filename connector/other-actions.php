<?php

if ($action == 'resetStars' && $target == 'tinyUser') {

	$response = array();

	$query = "UPDATE `{$tablePrefix}user`
	SET `starsNumber` = 0
	WHERE id = {$_POST['id']} && role = 0";	


	if(!$mysqli->query($query)) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
	} else {
		$response['error'] = false;
	}

	echo json_encode($response);	
}

if ($action == 'pay' && $target == 'appointment') {

	$response = array();
	$query = sprintf("UPDATE `{$tablePrefix}appointment` a SET `paid` = 1 WHERE `id` IN (%s)", $_POST['ids']);
	
	if(!$mysqli->query($query)) {
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
	} else {
		$response['error'] = false;
	}

	echo json_encode($response);	

}
if ($action == 'invoiceSeen' && $target == 'appointment') {

    $response = array();
    $query = sprintf("UPDATE `{$tablePrefix}appointment` a SET `invoiceSeen` = 1 WHERE `id` IN (%s)", $_POST['ids']);

    if(!$mysqli->query($query)) {
        $response['error'] = true;
        $response['data'] = $mysqli->error . "\nQuery: {$query}";
    } else {
        $response['error'] = false;
    }

    echo json_encode($response);

}

if($action == 'get_salesman' && $target == 'appointment'){
    $response = array();
    $relationLevel = array(REGIONAL_MANAGER_ROLE_ID,DISTRICT_MANAGER_ROLE_ID, SALES_MANAGER_ROLE_ID,SALESMAN_ROLE_ID);

    $roles  = join(',', $relationLevel);

    $sql_parents = "select id,  name, lastName, concat(name, ' ', lastName) as fullName , role from `{$tablePrefix}user` where deleted =0 and role IN (".$roles.") ORDER BY id DESC ";

    $result_parents = $mysqli->query($sql_parents);

    if($result_parents) {
        while ($row = $result_parents->fetch_assoc()) {
            $response['data'][] = $row;
        }
    }else{
        $response['error'] = true;
        $response['data'] = $mysqli->error . "\nQuery: {$query}";

    }
    echo json_encode($response);

//    if($logged_in_user_role == REGIONAL_MANAGER_ROLE_ID) {
//        $regionals = [];
//        $relationLevel = array(DISTRICT_MANAGER_ROLE_ID, SALES_MANAGER_ROLE_ID);
//        /**** Get Salesmen part *******/
//        $sql_salesmen = "select id from `{$tablePrefix}user` where deleted =0 and role = ".SALESMAN_ROLE_ID." and parent_user_id =".$logged_in_user_id;
//        $result_salesmen = $mysqli->query($sql_salesmen);
//        if($result_salesmen){
//            while ($row = $result_salesmen->fetch_assoc()) {
//                $salesman_lists_regional[] = $row['id'];
//            }
//        }
//        /*** Get Regional Part ****/
//
//        $sql_regional = "select id from `{$tablePrefix}user` where deleted =0 and role = ".DISTRICT_MANAGER_ROLE_ID." and parent_user_id =".$logged_in_user_id;
//        $result_regional_users = $mysqli->query($sql_regional);
//        if($result_regional_users) {
//            while ($row = $result_regional_users->fetch_assoc()) {
//                $regionals[] = $row['id'];
//            }
//        }
//        $regional_ids =  join(',', $regionals);
//
//
//        $roles  = join(',', $relationLevel);
//        $sql_parents = "select id from `{$tablePrefix}user` where deleted =0 and role IN ".$roles." and parent_user_id =".$logged_in_user_id;
//
//        $result_parents = $mysqli->query($sql_parents);
//        if($result_parents) {
//            while ($row = $result_parents->fetch_assoc()) {
//                $parents[] = $row['id'];
//            }
//        }
//        $parent_ids =  join(',', $parents);
//
//
//
//    }else if($logged_in_user_role == DISTRICT_MANAGER_ROLE_ID){
//        $relationLevel = array(SALES_MANAGER_ROLE_ID,SALESMAN_ROLE_ID);
//    }else if($logged_in_user_role == SALES_MANAGER_ROLE_ID){
//        $relationLevel = array(SALESMAN_ROLE_ID);
//    }




}

if ($action == 'get_level' && $target == 'user'){
    $response = array();
    $user_level = $_POST['user_level'];
    $relationLevel = array();

    if($user_level == SALESMAN_ROLE_ID){
        $relationLevel = array(REGIONAL_MANAGER_ROLE_ID,DISTRICT_MANAGER_ROLE_ID, SALES_MANAGER_ROLE_ID);
    }else if($user_level  == SALES_MANAGER_ROLE_ID){
        $relationLevel = [REGIONAL_MANAGER_ROLE_ID,DISTRICT_MANAGER_ROLE_ID];
    }else if($user_level == DISTRICT_MANAGER_ROLE_ID){
        $relationLevel = [REGIONAL_MANAGER_ROLE_ID];
    }
    $roles = join(',', $relationLevel);
    $query = "SELECT id, name, lastName, concat(name, ' ', lastName) as fullName , role, starsNumber FROM `{$tablePrefix}user` a WHERE deleted = 0 AND role IN (".$roles.") ORDER BY id DESC ";
    $result = $mysqli->query($query);
    if(!$result) {
        $response['error'] = true;
        $response['data'] = $mysqli->error . "\nQuery: {$query}";
    } else {
        $response['error'] = false;
        $response['data'] = array();
        while ($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
    }
    echo json_encode($response);

}
