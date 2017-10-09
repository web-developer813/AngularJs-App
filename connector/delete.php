<?php

	if ($action == 'delete') {
		if ($target == 'appointment') {
			$response = array();
			$query = "DELETE FROM `{$tablePrefix}appointment` WHERE id = {$_POST['id']}";
		}

		if ($target == 'user') {
			$response = array();
			$query = "UPDATE `{$tablePrefix}user` SET deleted = 1 WHERE id = {$_POST['id']}";		
		}

		if ($target == 'goal') {
			$response = array();
			$query = "DELETE FROM `{$tablePrefix}goal` WHERE id = {$_POST['id']}";
		}

		// Common part

		if(!$mysqli->query($query)) {
			$response['error'] = true;
			$response['data'] = $mysqli->error . "\nQuery: {$query}";
		} else {
			$response['error'] = false;
			if ($target !== 'goal')
				getCount($response);	
		}

		echo json_encode($response);
	}