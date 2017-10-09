<?php

 
include_once("../configs/config.php");

  //$tablePrefix = "appointments_";


class currentlocation
{
	private $TrackingID;
	private $UserID;
	private $Lng;
	private $Lat;
	private $IsActive;
	private $CurrentDateTime;
	private $Speed;
	private $LocationMethod;
	private $Distance;
	private $PhoneNumber;
	private $SessionID;
	private $Accuracy;
	private $EventType;
	private $Direction;
	public function __construct($TrackingID='')
	{
		$this->setTrackingID($TrackingID);
		$this->Load();
	}

	private function Load()
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

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
		$query = "SELECT * FROM " .$tablePrefix."currentlocation WHERE `TrackingID`='{$this->getTrackingID()}'";

		$result = mysqli_query($dblink,$query);

		while($row = mysqli_fetch_assoc($result) )
			foreach($row as $key => $value)
			{
				$column_name = str_replace('-','_',$key);
				$this->{"set$column_name"}($value);

			}
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
	}


public function ActiveCurrentLocation($TrackingID,$UserID)
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		try
		{
			$dblink = mysql_connect(DB_HOST,DB_USER,DB_PASS);
			mysql_select_db(DB_BASE,$dblink);
		}
		catch(Exception $ex)
		{
			echo "Could not connect to " . DB_HOST . ":" . DB_BASE . "\n";
			echo "Error: " . $ex->message;
			exit;
		}
		$query = "UPDATE " .$tablePrefix."currentlocation SET IsActive='0'  WHERE TrackingID<'".$TrackingID. "'   AND UserID='".$UserID. "'  ";

 		//echo $query;
 
		mysql_query($query,$dblink);

		if(is_resource($dblink)) mysql_close($dblink);
	}


	public static function GetActiveConnections()
	{
		/*
		$objCol = new collection();		
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		try
		{
			$dblink = mysql_connect(DB_HOST,DB_USER,DB_PASS);
		 	mysql_unbuffered_query('SET NAMES utf8');				
			mysql_select_db(DB_BASE,$dblink);
		}
		catch(Exception $ex)
		{
			echo "Could not connect to " . DB_HOST . ":" . DB_BASE . "\n";
			echo "Error: " . $ex->message;
			exit;
		}


$query ="SELECT employees.EmployeeName,employeestype.EmployeeTypeName,c.`DepartmentCode`,c.`DepartmentName`,cl.Lng,cl.Lat,cl.IsActive,cl.CurrentDateTime  
FROM employees INNER JOIN employeestype ON employees.EmployeeTypeID=employeestype.EmployeeTypeID LEFT JOIN costcode c ON employees.`CostCodeID`=c.`CostCodeID` LEFT JOIN currentlocation cl ON employees.EmployeeID=cl.UserID  WHERE cl.IsActive=1 ";



	if ($result = mysql_query ($query,$dblink)) 
   { 
    // resultset processing goes here 
    while ($row = mysql_fetch_assoc ($result)) 
    { 
	$obj = new currentlocation();
			
			foreach($row as $key => $value)
			{
				$column_name = str_replace('-','_',$key);
				//echo "," .$column_name; 
				$obj->{"set$column_name"}($value);
			}
			$objCol->add($obj);
			} 
		} 
		else 
		{ 
			echo (mysql_error ()); 
		} 
				if(is_resource($dblink)) mysql_close($dblink);
				return $objCol;
				
		*/
	}	




	public static function GetActiveSessions($SessionDate)
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		$rows = array();

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
		//$query = "SELECT * FROM " .$tablePrefix."currentlocation";
		
		$query ="SELECT S.* FROM
				(SELECT UserID,SessionID,MAX(TrackingID) AS MaxTrackingID,MIN(TrackingID) AS MinTrackingID
				FROM " .$tablePrefix."currentlocation 
				GROUP BY SessionID,UserID) AS S
				LEFT JOIN 
				(
				 SELECT TrackingID,SessionID,Lng,Lat,UserID,Speed,Distance,CurrentDateTime,IsActive 
				 FROM  " .$tablePrefix."currentlocation 
				) MX
				ON MX.TrackingID=S.MaxTrackingID";

		if ($result = mysqli_query($dblink,$query))

		{
			while($row = mysqli_fetch_assoc($result) )
				$rows[] = $row;
			}
		else 
		{ 
				echo (mysqli_error ($dblink));  
		} 
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		return $rows; 
	}


	public static function GetRowByTrackingID($ID,$type)
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		$rows = array();

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
		$query = "SELECT c.*,'".$type."' as `type`,u.name FROM " .$tablePrefix."currentlocation c LEFT JOIN " .$tablePrefix."user u ON u.id=c.`UserID` WHERE TrackingID=".$ID;

		if ($result = mysqli_query($dblink,$query))

		{
			while($row = mysqli_fetch_assoc($result) )
				$rows[] = $row;
			}
		else 
		{ 
				echo (mysqli_error ($dblink));  
		} 
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		return $rows; 
	}


	public static function GetAllRowsArray()
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		$rows = array();

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
		$query = "SELECT * FROM " .$tablePrefix."currentlocation";

		if ($result = mysqli_query($dblink,$query))

		{
			while($row = mysqli_fetch_assoc($result) )
				$rows[] = $row;
			}
		else 
		{ 
				echo (mysqli_error ($dblink));  
		} 
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		return $rows; 
	}

	public static function SearchStringInTable($type,$Text)
	{
		$dblink = null;
		global  $dbHost,$dbUser,$dbPass,$dbName,$tablePrefix;

		$rows = array();

		if($type=='equal')
			$search="='".$Text."'";
		elseif($type=='start')
			$search="LIKE'%".$Text."'";
		elseif($type=='any')
			$search="LIKE'%".$Text."%'";
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
		$query = "SELECT * FROM " .$tablePrefix."currentlocation WHERE 1=1 
				  AND " .$tablePrefix."currentlocation.TrackingID$search
				  OR " .$tablePrefix."currentlocation.UserID$search 
				  OR " .$tablePrefix."currentlocation.Lng$search 
				  OR " .$tablePrefix."currentlocation.Lat$search 
				  OR " .$tablePrefix."currentlocation.IsActive$search 
				  OR " .$tablePrefix."currentlocation.CurrentDateTime$search 
				  OR " .$tablePrefix."currentlocation.Speed$search 
				  OR " .$tablePrefix."currentlocation.LocationMethod$search 
				  OR " .$tablePrefix."currentlocation.Distance$search 
				  OR " .$tablePrefix."currentlocation.PhoneNumber$search 
				  OR " .$tablePrefix."currentlocation.SessionID$search 
				  OR " .$tablePrefix."currentlocation.Accuracy$search 
				  OR " .$tablePrefix."currentlocation.EventType$search 
				  OR " .$tablePrefix."currentlocation.Direction$search 
";

		if ($result = mysqli_query($dblink,$query))

		{
			while($row = mysqli_fetch_assoc($result) )
				$rows[] = $row;
			}
		else 
		{ 
				echo (mysqli_error ($dblink));  
		} 
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		return $rows; 
	}

	public static function GetValueByColumn($ColumnName,$ColumnValue,$RequiredColumn)
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
		$query = "SELECT $RequiredColumn FROM " .$tablePrefix."currentlocation WHERE $ColumnName='$ColumnValue' ";

		$result = mysqli_query($dblink,$query);
		$row = mysqli_fetch_row ($result);
		$res=$row[0];
		// Free result set
		mysqli_free_result($result);
		if(is_resource($dblink)) mysqli_close($dblink);
		 return $res;

	}

	public static function DeleteByID($IDValue)
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
		$query = "DELETE FROM  " .$tablePrefix."currentlocation WHERE `TrackingID`='$IDValue'";

		mysqli_query($dblink,$query);

		if(is_resource($dblink)) mysqli_close($dblink);
	}

	public function Update()
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
		$query = "UPDATE " .$tablePrefix."currentlocation SET 
						`UserID` = '" . mysqli_real_escape_string($dblink,$this->getUserID()) . "',
						`Lng` = '" . mysqli_real_escape_string($dblink,$this->getLng()) . "',
						`Lat` = '" . mysqli_real_escape_string($dblink,$this->getLat()) . "',
						`IsActive` = '" . mysqli_real_escape_string($dblink,$this->getIsActive()) . "',
						`CurrentDateTime` = '" . mysqli_real_escape_string($dblink,$this->getCurrentDateTime()) . "',
						`Speed` = '" . mysqli_real_escape_string($dblink,$this->getSpeed()) . "',
						`LocationMethod` = '" . mysqli_real_escape_string($dblink,$this->getLocationMethod()) . "',
						`Distance` = '" . mysqli_real_escape_string($dblink,$this->getDistance()) . "',
						`PhoneNumber` = '" . mysqli_real_escape_string($dblink,$this->getPhoneNumber()) . "',
						`SessionID` = '" . mysqli_real_escape_string($dblink,$this->getSessionID()) . "',
						`Accuracy` = '" . mysqli_real_escape_string($dblink,$this->getAccuracy()) . "',
						`EventType` = '" . mysqli_real_escape_string($dblink,$this->getEventType()) . "',
						`Direction` = '" . mysqli_real_escape_string($dblink,$this->getDirection()) . "' 
						WHERE `TrackingID`='{$this->getTrackingID()}'";

		mysqli_query($dblink,$query);

		if(is_resource($dblink)) mysqli_close($dblink);
	}

	public function Insert()
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
		$query ="INSERT INTO " .$tablePrefix."currentlocation (`CurrentDateTime`,`IsActive`, `UserID`,`Lng`,`Lat`,`Speed`,`LocationMethod`,`Distance`,`PhoneNumber`,`SessionID`,`Accuracy`,`EventType`,`Direction`) VALUES (
		CURRENT_TIMESTAMP,
		'" . mysqli_real_escape_string($dblink,$this->getIsActive()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getUserID()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getLng()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getLat()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getSpeed()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getLocationMethod()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getDistance()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getPhoneNumber()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getSessionID()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getAccuracy()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getEventType()) . "',
		'" . mysqli_real_escape_string($dblink,$this->getDirection()) . "');";
		mysqli_query($dblink,$query);

		$result = mysqli_insert_id($dblink);

		if(is_resource($dblink)) mysqli_close($dblink);
		if(!$result)
			return false;
		else
			return $result;
	}

	public function setTrackingID($TrackingID='')
	{
		$this->TrackingID = $TrackingID;
		return true;
	}

	public function getTrackingID()
	{
		return $this->TrackingID;
	}

	public function setUserID($UserID='')
	{
		$this->UserID = $UserID;
		return true;
	}

	public function getUserID()
	{
		return $this->UserID;
	}

	public function setLng($Lng='')
	{
		$this->Lng = $Lng;
		return true;
	}

	public function getLng()
	{
		return $this->Lng;
	}

	public function setLat($Lat='')
	{
		$this->Lat = $Lat;
		return true;
	}

	public function getLat()
	{
		return $this->Lat;
	}

	public function setIsActive($IsActive='')
	{
		$this->IsActive = $IsActive;
		return true;
	}

	public function getIsActive()
	{
		return $this->IsActive;
	}

	public function setCurrentDateTime($CurrentDateTime='')
	{
		$this->CurrentDateTime = $CurrentDateTime;
		return true;
	}

	public function getCurrentDateTime()
	{
		return $this->CurrentDateTime;
	}

	public function setSpeed($Speed='')
	{
		$this->Speed = $Speed;
		return true;
	}

	public function getSpeed()
	{
		return $this->Speed;
	}

	public function setLocationMethod($LocationMethod='')
	{
		$this->LocationMethod = $LocationMethod;
		return true;
	}

	public function getLocationMethod()
	{
		return $this->LocationMethod;
	}

	public function setDistance($Distance='')
	{
		$this->Distance = $Distance;
		return true;
	}

	public function getDistance()
	{
		return $this->Distance;
	}

	public function setPhoneNumber($PhoneNumber='')
	{
		$this->PhoneNumber = $PhoneNumber;
		return true;
	}

	public function getPhoneNumber()
	{
		return $this->PhoneNumber;
	}

	public function setSessionID($SessionID='')
	{
		$this->SessionID = $SessionID;
		return true;
	}

	public function getSessionID()
	{
		return $this->SessionID;
	}

	public function setAccuracy($Accuracy='')
	{
		$this->Accuracy = $Accuracy;
		return true;
	}

	public function getAccuracy()
	{
		return $this->Accuracy;
	}

	public function setEventType($EventType='')
	{
		$this->EventType = $EventType;
		return true;
	}

	public function getEventType()
	{
		return $this->EventType;
	}

	public function setDirection($Direction='')
	{
		$this->Direction = $Direction;
		return true;
	}

	public function getDirection()
	{
		return $this->Direction;
	}

} // END class
