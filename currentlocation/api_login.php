<?php


include_once("../configs/config.php");
	
	
$userid = $_POST["uID"];
$pass = $_POST["uPass"];


$id=0;

$id=LoginUserAPI($userid,$pass);

echo (int)$id;


 function LoginUserAPI($UserLogin,$Password)
	{
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;
		
		$dblink = null;

		try
		{
			$dblink = mysqli_connect( $dbHost,$dbUser,$dbPass);
			mysqli_select_db($dblink,$dbName);
		}
		catch(Exception $ex)
		{
			echo "Could not connect to " .  $dbHost . ":" . $dbName . "\n";
			echo "Error: " . $ex->message;
			exit;
		}
		
		$query = "select id from `{$tablePrefix}user` where email='".$UserLogin."' AND password='".$Password."' ";

				
		//echo $query;

		$result = mysqli_query($dblink,$query);
		$row = mysqli_fetch_row ($result);
		$res=$row[0];
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		 return $res;

	}

?>