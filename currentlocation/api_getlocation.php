<?php
include_once("currentlocation.class.php");

/*
$userid = $_GET["uID"];
$lng = $_GET["uLng"];
$lat = $_GET["uLat"];
$Speed= $_GET["speed"];
$LocationMethod= $_GET["locationmethod"];
$Distance= $_GET["distance"];
$PhoneNumber= $_GET["phonenumber"];
$SessionID= $_GET["sessionid"];
$Accuracy= $_GET["accuracy"];
$EventType= $_GET["eventtype"];
$Direction= $_GET["direction"];
*/


$userid = $_POST["uID"];
$lng = $_POST["uLng"];
$lat = $_POST["uLat"];
$Speed= $_POST["speed"];
$LocationMethod= $_POST["locationmethod"];
$Distance= $_POST["distance"];
$PhoneNumber= $_POST["phonenumber"];
$SessionID= $_POST["sessionid"];
$Accuracy= $_POST["accuracy"];
$EventType= $_POST["eventtype"];
$Direction= $_POST["direction"];


if ( (int)$lng<=0 && (int)$lat<=0)
{
	echo "-1";
	exit();	
}
$obj = new currentlocation();

$obj->setUserID($userid);
$obj->setLat ($lat);
$obj->setLng( $lng);
$obj->setIsActive(1);
$obj->setSpeed($Speed);
$obj->setLocationMethod($LocationMethod);
$obj->setDistance($Distance);
$obj->setPhoneNumber($PhoneNumber);
$obj->setSessionID($SessionID);
$obj->setAccuracy($Accuracy);
$obj->setEventType($EventType);
$obj->setDirection($Direction);

$id = $obj->Insert();
if ((int)$id>0)
{
	$obj->ActiveCurrentLocation($id,$userid);
	echo  $id;
}
else
{
	echo "-1";	
}

?>