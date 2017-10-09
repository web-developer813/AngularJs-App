<?php

include_once("currentlocation.class.php");

$DeviceSessions = currentlocation::GetActiveSessions("");
$Data = array();
$TrackingID;
$row=null;
$rowMAX;
$rowMIN;
$resultstr;
foreach($DeviceSessions as $k=>$v)
{ 
	$TrackingID = $v["MaxTrackingID"];
	if ((int)$TrackingID>0)
	{
		$rowMAX=null;
		$rows= currentlocation::GetRowByTrackingID($TrackingID,"salesman");
		if ($rows!=null && $rows[0]!=null)
		{
			$rowMAX=$rows[0];
		}
	}
	$TrackingID = $v["MinTrackingID"];
	if ((int)$TrackingID>0)
	{	$rowMin=null;
		$rows= currentlocation::GetRowByTrackingID($TrackingID,"salesman");
		if ($rows!=null && $rows[0]!=null)
		{
			$rowMIN=$rows[0];
		}	
	}

	if($rowMAX!=null && $rowMIN!=null && trim($rowMAX['SessionID'])==trim($rowMIN['SessionID']) && (int)$rowMAX['UserID']==(int)$rowMIN['UserID'])
	{

		$arr = array();
		$lat=$rowMAX['Lat'];
		$lng=$rowMAX['Lng'];
		$desc =$rowMAX['name']."<br/>Total Distance(miles):".$rowMAX['Distance']."<br/>Speed(miles/hour):".$rowMAX['Speed']."<br/>Accuracy(%):".$rowMAX['Accuracy']
		."<br/>Device:".$rowMAX['EventType']."<br/>Last tracked at:".$rowMAX['CurrentDateTime']."<br/>Tracking started at:".$rowMIN['CurrentDateTime'];
		
		$arr[] =array ('lat'=> "$lat",'lng'=> "$lng",'desc'=> "$desc");
		 array_push($Data, $arr);		
	}		
}

	$result= json_encode($Data);
	$result =str_replace("[{","{",$result);
	$result =str_replace("}]","}",$result);
	echo $result;
?>