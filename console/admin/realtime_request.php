<?php
set_time_limit(0);

include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once("function_admin.php");


$client_ip = PubFunc_GetRemortIP();
if($FikConfig_TaskIsLocalRun)
{
	if($client_ip!="127.0.0.1")
	{
		echo "Forbidden";
		exit();
	}
}

echo "ip=".$client_ip."<br />";
$begin_time = time();

exit();	
$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod=="realtime")
{
	if($sAction=="list")
	{		
		$group_id 		= isset($_GET['group_id'])?$_GET['group_id']:'';
		
		if(strlen($group_id)>0)
		{
			if(!is_numeric($group_id))
			{
				exit();
			}	
			
			$sql_and = "AND groupid=$group_id";
		}
		
		$total_down_increase =0;
		$total_up_increase =0;
		$total_bandwidth_down=0;
		$total_bandwidth_up=0;
		
		$total_upstream_down_increase =0;
		$total_upstream_up_increase =0;
		$total_upstream_bandwidth_down=0;
		$total_upstream_bandwidth_up=0;	
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{			
			$sql ="SELECT * FROM fikcdn_params WHERE name='realtime_list_enter'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				echo "realtime list begin run 2</br>";
				mysql_close($db_link);
				exit();
			}
			
			if(mysql_num_rows($result)<=0)
			{
				$timenow = time();
				$sql = "INSERT INTO fikcdn_params(id,name,value,time) VALUE(NULL,'realtime_list_enter','1','$timenow')";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					mysql_close($db_link);
					exit();
				}	
			}
			else
			{
				$timenow = time();
				
				$value = mysql_result($result,0,"value");
				$time = mysql_result($result,0,"time");
				
				if($value == '1' && (time()-$time)<=1200 )
				{
					echo "realtime_list_enter=1</br>";
					mysql_close($db_link);
					exit();	
				}
					
				$sql = "UPDATE fikcdn_params SET value='1',time='$timenow' WHERE name='realtime_list_enter'";
				$result = mysql_query($sql,$db_link);	
				if(!$result)
				{
					mysql_close($db_link);
					exit();
				}	
			}		
					
			//查找所有开放的节点
			$sql = "SELECT * FROM fikcdn_node WHERE is_close=0 $sql_and";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrQuery);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$nTime = mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y"));
			
			$rows_count = mysql_num_rows($result);
			for($i=0;$i<$rows_count;$i++)
			{
				$node_id 		= mysql_result($result,$i,"id");	
				$name   		= mysql_result($result,$i,"name");	
				$ip  		 	= mysql_result($result,$i,"ip");
				$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");		
				$port   		= mysql_result($result,$i,"port");	
				$admin_port   	= mysql_result($result,$i,"admin_port");	
				$password   	= mysql_result($result,$i,"password");
				$SessionID   	= mysql_result($result,$i,"SessionID");	
				$auth_domain   	= mysql_result($result,$i,"auth_domain");
				$groupid	   	= mysql_result($result,$i,"groupid");
				$status	   		= mysql_result($result,$i,"status");		
				
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0){
					$sFikIP = $unicom_ip;
				}
				
				$aryFikResult = fikapi_realtimelist($sFikIP,$admin_port,$SessionID);
				if($aryFikResult==false){
					$aryFikResult = fikapi_realtimelist($sFikIP,$admin_port,$SessionID);
					echo "connect to fikker failed, reconnect. .ip=$sFikIP.<br />";
					if($aryFikResult==false){
							echo "reconnect to fikker failed.ip=$sFikIP.<br /><br />";
					}
				}
				//echo "<br><br>aryFikResult=";var_dump($aryFikResult);
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$SessionID = $aryRelogin["SessionID"];
							$aryFikResult=fikapi_realtimelist($sFikIP,$admin_port,$SessionID);
							//echo "<br><br>aryFikResult=";var_dump($aryFikResult);
						}
					}
				}
				
				$down_increase =0;
				$up_increase =0;
				$bandwidth_down=0;
				$bandwidth_up=0;
				
				$upstream_down_increase =0;
				$upstream_up_increase =0;
				$upstream_bandwidth_down=0;
				$upstream_bandwidth_up=0;				
				
				if($aryFikResult["Return"]=="True")
				{
					/*
					$strTime = $aryFikResult['CurrentTime'];
					$nTime strtotime($strTime);
					*/
					$StartTime= $aryFikResult['StartTime'];
					$EndTime= $aryFikResult['CurrentTime'];
					$CurrentUserConnections = $aryFikResult['CurrentUserConnections'];
					$CurrentUpstreamConnections = $aryFikResult['CurrentUpstreamConnections'];
					$AllUsedMemSize = $aryFikResult['AllUsedMemSize'];
					$CacheUsedMemSize = $aryFikResult['CacheUsedMemSize'];
					$NumOfCaches = $aryFikResult['NumOfCaches'];
					$TotalSendKB = $aryFikResult['TotalSendKB'];
					$TotalRecvKB = $aryFikResult['TotalRecvKB'];
					$NumOfCachedSessions = $aryFikResult['NumOfCachedSessions'];
					$NumOfPublicCaches = $aryFikResult['NumOfPublicCaches'];
					$NumOfMemberCaches = $aryFikResult['NumOfMemberCaches'];
					$NumOfVisitorCaches = $aryFikResult['NumOfVisitorCaches'];
					$PublicCacheUsedMemSize = $aryFikResult['PublicCacheUsedMemSize'];
					$MemberCacheUsedMemSize = $aryFikResult['MemberCacheUsedMemSize'];
					$VisitorCacheUsedMemSize = $aryFikResult['VisitorCacheUsedMemSize'];
					$TotalSendToResponseKB = $aryFikResult['TotalSendToResponseKB'];
					$TotalRecvFromResponseKB = $aryFikResult['TotalRecvFromResponseKB'];
					
					$nStartTime = strtotime($StartTime);
					$nEndTime = strtotime($EndTime);
					
					//echo '<br>TotalSendKB='.$TotalSendKB;
					
					$sql ="SELECT * FROM realtime_list WHERE node_id='$node_id' ORDER BY id DESC Limit 1";
					//echo "<br>sql=".$sql;
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$nPrevtime	= mysql_result($result2,0,"time");	
						$nPrevStartTime   = mysql_result($result2,0,"StartTime");	
						$nPrevEndTime   	= mysql_result($result2,0,"EndTime");	
						$nPrevTotalSendKB   	= mysql_result($result2,0,"TotalSendKB");
						$nPrevTotalRecvKB   	= mysql_result($result2,0,"TotalRecvKB");
						$nPrevTotalSendToResponseKB  	= mysql_result($result2,0,"TotalSendToResponseKB");
						$nPrevTotalRecvFromResponseKB  	= mysql_result($result2,0,"TotalRecvFromResponseKB");
						
						if($nPrevStartTime==$nStartTime)
						{
							//echo '<br>1. nPrevTotalSendKB='.$nPrevTotalSendKB;
							//echo '<br>1. nPrevStartTime='.$nPrevStartTime;
							
							if(($nEndTime-$nPrevEndTime)>0){
								$down_increase =$TotalSendKB-$nPrevTotalSendKB;
								$up_increase =$TotalRecvKB-$nPrevTotalRecvKB;
							
								$bandwidth_down = ($down_increase)/($nEndTime-$nPrevEndTime);
								$bandwidth_up = ($up_increase)/($nEndTime-$nPrevEndTime);
								
								$upstream_down_increase =$TotalSendToResponseKB-$nPrevTotalSendToResponseKB;
								$upstream_up_increase =$TotalRecvFromResponseKB-$nPrevTotalRecvFromResponseKB;
								
								$upstream_bandwidth_down = $upstream_down_increase/($nEndTime-$nPrevEndTime);
								$upstream_bandwidth_up = $upstream_up_increase/($nEndTime-$nPrevEndTime);
							}
						}
						else
						{
							//echo '<br>2. nPrevTotalSendKB='.$nPrevTotalSendKB;						
							//echo '<br>2. nPrevStartTime='.$nPrevStartTime;
							//echo '<br>2. nStartTime='.$nStartTime;							
							if(($nEndTime-$nStartTime)>0){
								$down_increase =$TotalSendKB;
								$up_increase =$TotalRecvKB;
								$bandwidth_down = ($TotalSendKB)/($nEndTime-$nStartTime);
								$bandwidth_up = ($TotalRecvKB)/($nEndTime-$nStartTime);
								
								$upstream_down_increase =$TotalSendToResponseKB;
								$upstream_up_increase =$TotalRecvFromResponseKB;
								
								$upstream_bandwidth_down = $upstream_down_increase/($nEndTime-$nStartTime);
								$upstream_bandwidth_up = $upstream_up_increase/($nEndTime-$nStartTime);
							} 
						}
					}
					else
					{
						//echo "<br>".mysql_error($db_link);
						//echo '<br>3. TotalSendKB='.$TotalSendKB;						
						//echo '<br>3. nStartTime='.$nStartTime;
						//echo '<br>3. nEndTime='.$nEndTime;						
					
						if(($nEndTime-$nStartTime)>0){
							$down_increase =$TotalSendKB;
							$up_increase =$TotalRecvKB;
							$bandwidth_down = ($TotalSendKB)/($nEndTime-$nStartTime);
							$bandwidth_up = ($TotalRecvKB)/($nEndTime-$nStartTime);
							
							$upstream_down_increase =$TotalSendToResponseKB;
							$upstream_up_increase =$TotalRecvFromResponseKB;
							
							$upstream_bandwidth_down = $upstream_down_increase/($nEndTime-$nStartTime);
							$upstream_bandwidth_up = $upstream_up_increase/($nEndTime-$nStartTime);
						}
					}
					
					$bandwidth_down = round(($bandwidth_down*8)/1024,2);
					$bandwidth_up = round(($bandwidth_up*8)/1024,2);
					if(strlen($down_increase)<=0) $down_increase=0;
					if(strlen($up_increase)<=0) $up_increase=0;
					
					$upstream_bandwidth_down = round(($upstream_bandwidth_down*8)/1024,2);
					$upstream_bandwidth_up = round(($upstream_bandwidth_up*8)/1024,2);
					if(strlen($upstream_down_increase)<=0) $upstream_down_increase=0;
					if(strlen($upstream_up_increase)<=0) $upstream_up_increase=0;
					
					$total_bandwidth_down += $bandwidth_down;
					$total_bandwidth_up += $bandwidth_up;	
					$total_down_increase += $down_increase;	
					$total_up_increase += $up_increase;
					
					$total_upstream_bandwidth_down += $upstream_bandwidth_down;
					$total_upstream_bandwidth_up += $upstream_bandwidth_up;
					$total_upstream_down_increase += $upstream_down_increase;
					$total_upstream_up_increase += $upstream_up_increase;																					
					
					$nBWOffset = 1.1;					
					$bandwidth_down = 	round($bandwidth_down*$nBWOffset,2);
					$bandwidth_up = 	round($bandwidth_up*$nBWOffset,2);										
				
					$sql = "INSERT INTO realtime_list(id,group_id,node_id,time,StartTime,EndTime,CurrentUserConnections,CurrentUpstreamConnections,AllUsedMemSize,CacheUsedMemSize,NumOfCaches,TotalSendKB,
							TotalRecvKB,NumOfCachedSessions,NumOfPublicCaches,NumOfMemberCaches,NumOfVisitorCaches,PublicCacheUsedMemSize,MemberCacheUsedMemSize,VisitorCacheUsedMemSize,
							TotalSendToResponseKB,TotalRecvFromResponseKB,bandwidth_down,bandwidth_up,down_increase,up_increase,upstream_bandwidth_down,upstream_bandwidth_up,upstream_down_increase,upstream_up_increase)							
				            VALUE(NULL,'$groupid','$node_id','$nTime',$nStartTime,$nEndTime,'$CurrentUserConnections','$CurrentUpstreamConnections','$AllUsedMemSize',
							'$CacheUsedMemSize','$NumOfCaches','$TotalSendKB','$TotalRecvKB','$NumOfCachedSessions','$NumOfPublicCaches','$NumOfMemberCaches',
							'$NumOfVisitorCaches','$PublicCacheUsedMemSize','$MemberCacheUsedMemSize','$VisitorCacheUsedMemSize','$TotalSendToResponseKB',
							'$TotalRecvFromResponseKB','$bandwidth_down','$bandwidth_up','$down_increase','$up_increase','$upstream_bandwidth_down','$upstream_bandwidth_up','$upstream_down_increase','$upstream_up_increase')" ;
					if(!mysql_query($sql,$db_link))
					{
						echo mysql_error().'<br />';
					}			
				}
			}
			
			// MB
			$total_down_increase = round(($total_down_increase)/1024,2);
			$total_up_increase = round(($total_up_increase)/1024,2);		
			$total_upstream_down_increase = round(($total_upstream_down_increase)/1024,2);		
			$total_upstream_up_increase = round(($total_upstream_up_increase)/1024,2);
						
			// MB
			$sql = "INSERT INTO realtime_list_all(id,time,bandwidth_down,bandwidth_up,user_down,user_up,upstream_bandwidth_down,upstream_bandwidth_up,upstream_down,upstream_up) 
					VALUES(NULL,'$nTime','$total_bandwidth_down','$total_bandwidth_up','$total_down_increase','$total_up_increase','$total_upstream_bandwidth_down','$total_upstream_bandwidth_up','$total_upstream_down_increase','$total_upstream_up_increase')";
			$result3 = mysql_query($sql,$db_link);	
			if(!$result3)
			{
				echo mysql_error($db_link);
			}
			
			$timenow = time();
			$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='realtime_list_enter'";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				echo "UPDATE fikcdn_params error,".mysql_error($db_link);
			}					
						
			mysql_close($db_link);
		}
	}
	else if($sAction=="totalstat")
	{
		$group_id 		= isset($_GET['group_id'])?$_GET['group_id']:'';
		
		if(strlen($group_id)>0)
		{
			if(!is_numeric($group_id))
			{
				exit();
			}	
			
			$sql_and = "AND groupid=$group_id";
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{			
			//查找所有开放的节点
			$sql = "SELECT * FROM fikcdn_node WHERE is_close=0 $sql_and";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrQuery);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
	
			$nTimeNow = mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y"));
			
			$rows_count = mysql_num_rows($result);
			for($i=0;$i<$rows_count;$i++)
			{
				$node_id		= mysql_result($result,$i,"id");	
				$name   		= mysql_result($result,$i,"name");	
				$ip  		 	= mysql_result($result,$i,"ip");
				$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");		
				$port   		= mysql_result($result,$i,"port");	
				$admin_port   	= mysql_result($result,$i,"admin_port");	
				$password   	= mysql_result($result,$i,"password");
				$SessionID   	= mysql_result($result,$i,"SessionID");	
				$auth_domain   	= mysql_result($result,$i,"auth_domain");
				$groupid	   	= mysql_result($result,$i,"groupid");
				$status	   		= mysql_result($result,$i,"status");		
			
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0){
					$sFikIP = $unicom_ip;
				}
				
				$aryFikResult = fikapi_realtimetotalstat($sFikIP,$admin_port,$SessionID);
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$SessionID = $aryRelogin["SessionID"];						
							$aryFikResult=fikapi_realtimetotalstat($sFikIP,$admin_port,$SessionID);
						}
					}
				}
				
				if($aryFikResult["Return"]=="True")
				{
					$strTime = $aryFikResult['StartTime'];
					$nStartTime = strtotime($strTime);
					
					$strTime = $aryFikResult['EndTime'];
					$nEndTime=  strtotime($strTime);
					
					/*
					$year = substr($strTime,0,4);
					$month = substr($strTime,5,2);
					$day = substr($strTime,8,2);
					$hour = substr($strTime,11,2);
					$minute = substr($strTime,14,2);
					$second = substr($strTime,17,2);
					$nEndTime = mktime($hour,$minute,$second,$month,$day,$year);
					*/				
					$HitCachesRate = $aryFikResult['HitCachesRate'];
					$RealTimeReport = trim($aryFikResult['RealTimeReport']);
					echo $RealTimeReport.'<br>';
					$HitCachesRate = substr($HitCachesRate,0,strrpos($HitCachesRate,"%"));
					//echo $HitCachesRate.'  ';
					
					$aryStat = explode("\r\n",$RealTimeReport);
					$nAryCount = count($aryStat);
	
					$strReport = trim($aryStat[$nAryCount-1]);
					$arytotal2 = explode("   ",$strReport);
				
					$arytotal = array();
					$nCount2 = count($arytotal2);
					$h=0;
					for($k=0;$k<$nCount2;$k++)
					{
						if(strlen(trim($arytotal2[$k]))>0)
						{
							$arytotal[$h]=trim($arytotal2[$k]);
							$h++;
						}
					}
					
					//print_r($arytotal);
					//echo " .count=".count($arytotal)."<p />";
					$sql = "INSERT INTO realtime_totalstat(id,group_id,node_id,time,StartTime,EndTime,HitCachesRate,PV,TR,IP,PR,RealTimeReport) 
							VALUE(NULL,'$groupid','$node_id','$nTimeNow','$nStartTime','$nEndTime','$HitCachesRate','0','$arytotal[1]','$arytotal[2]','$arytotal[3]','$RealTimeReport');";
					//echo $sql;
					if(!mysql_query($sql,$db_link))
					{
						echo mysql_error().'<br />';
					}
				}
			}						
			mysql_close($db_link);	
		}
	}	
}
else if($sMod=="proxy")
{
	if($action="list")
	{
		$group_id 		= isset($_GET['group_id'])?$_GET['group_id']:'';
		
		if(strlen($group_id)>0)
		{
			if(!is_numeric($group_id))
			{
				exit();
			}	
			
			$sql_and = "AND groupid=$group_id";
		}
		
		echo "proxy list begin run</br>";
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql ="SELECT * FROM fikcdn_params WHERE name='proxy_list_enter'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				echo "proxy list begin run 2</br>";
				mysql_close($db_link);
				exit();
			}
			
			if(mysql_num_rows($result)<=0)
			{
				$timenow = time();
				$sql = "INSERT INTO fikcdn_params(id,name,value,time) VALUE(NULL,'proxy_list_enter','1','$timenow')";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					mysql_close($db_link);
					exit();
				}	
			}
			else
			{
				$timenow = time();
				
				$value = mysql_result($result,0,"value");
				$time = mysql_result($result,0,"time");
				
				if($value == '1' && (time()-$time)<=1200 )
				{
					echo "proxy_list_enter=1</br>";
					mysql_close($db_link);
					exit();	
				}
					
				$sql = "UPDATE fikcdn_params SET value='1',time='$timenow' WHERE name='proxy_list_enter'";
				$result = mysql_query($sql,$db_link);	
				if(!$result)
				{
					mysql_close($db_link);
					exit();
				}	
			}
				
			//查找所有开放的节点
			$sql = "SELECT * FROM fikcdn_node WHERE is_close=0 $sql_and";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='proxy_list_enter'";
				$result = mysql_query($sql,$db_link);	
				
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrQuery);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}

			$nTime = mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y"));
			$nDayTime = mktime(0,0,0,date("m"),date("d"),date("Y"));
			
			echo "$nTime=".date("Y-m-d H:i:s",$nTime)."<br/>";
			echo "$nDayTime=".date("Y-m-d H:i:s",$nDayTime)."<br/>";
			
			$aryHostSum = array();
			
			echo "<br>sql=".$sql.'<br />';

			$rows_count = mysql_num_rows($result);
			for($i=0;$i<$rows_count;$i++)
			{
				$node_id		= mysql_result($result,$i,"id");	
				$name   		= mysql_result($result,$i,"name");	
				$ip  		 	= mysql_result($result,$i,"ip");
				$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");	
				$port   		= mysql_result($result,$i,"port");	
				$admin_port   	= mysql_result($result,$i,"admin_port");	
				$password   	= mysql_result($result,$i,"password");
				$SessionID   	= mysql_result($result,$i,"SessionID");	
				$auth_domain   	= mysql_result($result,$i,"auth_domain");
				$groupid	   	= mysql_result($result,$i,"groupid");
				$status	   		= mysql_result($result,$i,"status");	
				
				echo "<br>node_id=".$node_id.'<br />';
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0){
					$sFikIP = $unicom_ip;
				}
				
				$aryFikResult = FikApi_ProxyList($sFikIP,$admin_port,$SessionID);
				if($aryFikResult == false){
					$aryFikResult = FikApi_ProxyList($sFikIP,$admin_port,$SessionID);
					echo "connect to fikker failed.reconnect. ip=$sFikIP.<br />";
					if($aryFikResult==false){
						echo "reconnect to fikker failed. ip=$sFikIP.<br /><br />";
					}
				}
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$SessionID = $aryRelogin["SessionID"];
							$aryFikResult=FikApi_ProxyList($sFikIP,$admin_port,$SessionID);
						}
					}
				}
				
				$down_increase =0;$up_increase =0;
				$RequestCount_increase =0;$IpCount_increase =0;
				
				//print_r($aryFikResult);
				if($aryFikResult["Return"]=="True")
				{
					$nNumOfLists = $aryFikResult["NumOfLists"];
					for($k=0;$k<$nNumOfLists;$k++)
					{
						$nNo = $aryFikResult["Lists"][$k]["NO"];
						if(strlen($nNo)<=0) $nNo = $aryFikResult["Lists"][$k]["No"];
						$nProxyID = $aryFikResult["Lists"][$k]["ProxyID"];
						$sHost = $aryFikResult["Lists"][$k]["Host"];
						$nBalance = $aryFikResult["Lists"][$k]["Balance"];
						$bEnable = $aryFikResult["Lists"][$k]["Enable"];
						$StartTime = $aryFikResult["Lists"][$k]["StartTime"];
						$EndTime = $aryFikResult["Lists"][$k]["EndTime"];
						$RequestCount = $aryFikResult["Lists"][$k]["RequestCount"];
						$UploadCount = $aryFikResult["Lists"][$k]["UploadCount"];
						$DownloadCount = $aryFikResult["Lists"][$k]["DownloadCount"];
						$IpCount = $aryFikResult["Lists"][$k]["IpCount"];
						$UserConnections = $aryFikResult["Lists"][$k]["UserConnections"];
						$UpstreamConnections = $aryFikResult["Lists"][$k]["UpstreamConnections"];
						$Note = $aryFikResult["Lists"][$k]["Note"];
						
						if(strlen($bEnable)<=0) $bEnable=1;
						if(strlen($RequestCount)<=0) $RequestCount=0;
						if(strlen($UploadCount)<=0) $UploadCount=0;
						if(strlen($DownloadCount)<=0) $DownloadCount=0;
						if(strlen($IpCount)<=0) $IpCount=0;
						if(strlen($UserConnections)<=0) $UserConnections=0;
						if(strlen($UpstreamConnections)<=0) $UpstreamConnections=0;						
						
						$strTime = $StartTime;
						$nStartTime = strtotime($strTime);

						$strTime = $EndTime;
						$nEndTime = strtotime($strTime);
							
						$down_increase =0;$up_increase =0;
						$bandwidth_down =0;$bandwidth_up =0;
						
						$bTempTablesOk = false;
							
						$sql ="SELECT * FROM domain_stat_temp WHERE node_id='$node_id' AND Host='$sHost'";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{
							$nPrevID	= mysql_result($result2,0,"id");	
							$nPrevtime	= mysql_result($result2,0,"time");	
							$nPrevStartTime   = mysql_result($result2,0,"StartTime");	
							$nPrevEndTime   	= mysql_result($result2,0,"EndTime");
							$nPrevRequestCount = mysql_result($result2,0,"RequestCount");	
							$nPrevDownloadCount = mysql_result($result2,0,"DownloadCount");
							$nPrevUploadCount   = mysql_result($result2,0,"UploadCount");
							$nPrevIpCount   = mysql_result($result2,0,"IpCount");
							$nPrevDownIncrease   = mysql_result($result2,0,"down_increase");
							$nPrevUpIncrease   = mysql_result($result2,0,"up_increase");
							$nPrevBandwidthDown   = mysql_result($result2,0,"bandwidth_down");
							$nPrevBandwidthUp   = mysql_result($result2,0,"bandwidth_up");
							
							$bTempTablesOk = true;
							
							if($nPrevStartTime==$nStartTime)
							{
								if(($nEndTime-$nPrevEndTime)>0){ 
									$down_increase =$DownloadCount-$nPrevDownloadCount;
									$up_increase=$UploadCount-$nPrevUploadCount;
									$RequestCount_increase =$RequestCount-$nPrevRequestCount;
									$IpCount_increase =$IpCount-$nPrevIpCount;
									
									$bandwidth_down = ($down_increase)/($nEndTime-$nPrevEndTime);
									$bandwidth_up = ($up_increase)/($nEndTime-$nPrevEndTime);
									
									//echo "1. bandwidth_up=".$bandwidth_up.",up_increaset=".$up_increase.",nEndTime-nPrevEndTime=".($nEndTime-$nPrevEndTime)."<br /><br />";
								}
							}
							else
							{
								if(($nEndTime-$nStartTime)>0){
									$down_increase =$DownloadCount;
									$up_increase=$UploadCount;									
									$RequestCount_increase =$RequestCount;
									$IpCount_increase =$IpCount;
									$bandwidth_down = ($DownloadCount)/($nEndTime-$nStartTime);
									$bandwidth_up = ($UploadCount)/($nEndTime-$nStartTime);
									//echo "2. bandwidth_up=".$bandwidth_up."<br /><br />";
								}
							}
						}			
						else
						{
							if(($nEndTime-$nStartTime)>0){ 
								$down_increase =0;
								$up_increase=0;									
								$RequestCount_increase =0;
								$IpCount_increase =0;
								$bandwidth_down = 0;//($DownloadCount)/($nEndTime-$nStartTime);
								$bandwidth_up = 0;//($UploadCount)/($nEndTime-$nStartTime);
								//echo "3. bandwidth_up=".$bandwidth_up."<br /><br />";
							}
						}																																		
						
						// MB
						$bandwidth_down = round(($bandwidth_down*8)/(1024*1024),2);
						$bandwidth_up = round(($bandwidth_up*8)/(1024*1024),2);
						//echo "4. bandwidth_up=".$bandwidth_up."<br /><br />";
						
						if(strlen($nStartTime)<=0) $nStartTime=0;
						if(strlen($nEndTime)<=0) $nEndTime=0;
						
						if(strlen($down_increase)<=0) $down_increase=0;
						if(strlen($up_increase)<=0) $up_increase=0;		
						
						
						if(strlen($aryHostSum[$groupid][$sHost]["bandwidth_down"])<=0){
							$aryHostSum[$groupid][$sHost]["bandwidth_down"]=$bandwidth_down;
							//echo "1. sum_bandwidth_down=".$aryHostSum[$groupid][$sHost]["bandwidth_down"]."<br /><br />";
						}else{
							$aryHostSum[$groupid][$sHost]["bandwidth_down"] += $bandwidth_down;
							//echo "2. sum_bandwidth_down=".$aryHostSum[$groupid][$sHost]["bandwidth_down"]."<br /><br />";
						}
						
						if(strlen($aryHostSum[$groupid][$sHost]["bandwidth_up"])<=0){
							$aryHostSum[$groupid][$sHost]["bandwidth_up"]=$bandwidth_up;
							//echo "1. sum_bandwidth_up=".$aryHostSum[$groupid][$sHost]["bandwidth_up"]."<br /><br />";
						}else{
							$aryHostSum[$groupid][$sHost]["bandwidth_up"] += $bandwidth_up;
							//echo "2. sum_bandwidth_up=".$aryHostSum[$groupid][$sHost]["bandwidth_up"]."<br /><br />";
						}
						
						if(strlen($aryHostSum[$groupid][$sHost]["RequestCount"])<=0){
							$aryHostSum[$groupid][$sHost]["RequestCount"]=$RequestCount_increase;
						}else{
							$aryHostSum[$groupid][$sHost]["RequestCount"]+=$RequestCount_increase;
						}
						
						if(strlen($aryHostSum[$groupid][$sHost]["IpCount"])<=0){
							$aryHostSum[$groupid][$sHost]["IpCount"]=$IpCount_increase;
						}else{
							$aryHostSum[$groupid][$sHost]["IpCount"]+=$IpCount_increase;
						}
						
						if(strlen($aryHostSum[$groupid][$sHost]["DownloadCount"])<=0){
							$aryHostSum[$groupid][$sHost]["DownloadCount"]=$down_increase;
						}else{
							$aryHostSum[$groupid][$sHost]["DownloadCount"]+=$down_increase;
						}
						
						if(strlen($aryHostSum[$groupid][$sHost]["UploadCount"])<=0){
							$aryHostSum[$groupid][$sHost]["UploadCount"]=$up_increase;
						}else{
							$aryHostSum[$groupid][$sHost]["UploadCount"]+=$up_increase;
						}												
												
						if(!$bTempTablesOk)
						{		
							$sql = "INSERT INTO domain_stat_temp(id,group_id,node_id,time,NO,ProxyID,Host,Balance,Enable,StartTime,EndTime,RequestCount,
								UploadCount,DownloadCount,IpCount,bandwidth_down,bandwidth_up,down_increase,up_increase,UserConnections,UpstreamConnections,RequestCount_increase) 
							VALUE(NULL,'$groupid','$node_id','$nTime','$nNo','$nProxyID','$sHost','$nBalance','$bEnable','$nStartTime','$nEndTime',
								'$RequestCount','$UploadCount','$DownloadCount','$IpCount','$bandwidth_down','$bandwidth_up',$down_increase,$up_increase,$UserConnections,$UpstreamConnections,$RequestCount_increase);";
							
							//echo $sql.'<br /><br />';
							if(!mysql_query($sql,$db_link))
							{
								echo mysql_error().'<br />INSERT domain_stat_temp'.$sql.'<br />';
							}						
						}
						else
						{
							$sql = "UPDATE domain_stat_temp SET time='$nTime',ProxyID='$nProxyID',NO='$nNo',Enable='$bEnable',StartTime='$nStartTime',EndTime='$nEndTime',RequestCount='$RequestCount',
									 UploadCount='$UploadCount',DownloadCount='$DownloadCount',IpCount='$IpCount',bandwidth_down='$bandwidth_down',
									bandwidth_up='$bandwidth_up',down_increase='$down_increase',up_increase='$up_increase',UserConnections=$UserConnections,UpstreamConnections=$UpstreamConnections,RequestCount_increase=$RequestCount_increase WHERE id='$nPrevID'";
							if(!mysql_query($sql,$db_link))
							{
								echo mysql_error().'<br /> UPDATE domain_stat_temp'.$sql.'<br />';
							}										
						}	
						
						/*
						//查询按天统计的
						$sql = "select * from domain_stat_day where time=$nDayTime AND node_id=$node_id AND Host='$sHost'";
						$result3 = mysql_query($sql,$db_link);
						if($result3 && mysql_num_rows($result3)>0)
						{
							$this_day_id	= mysql_result($result3,0,"id");
							$this_day_time	= mysql_result($result3,0,"time");	
							$this_day_StartTime = mysql_result($result3,0,"StartTime");	
							$this_day_EndTime   = mysql_result($result3,0,"EndTime");
							$this_day_RequestCount   = mysql_result($result3,0,"RequestCount");	
							$this_day_UploadCount   = mysql_result($result3,0,"UploadCount");	
							$this_day_DownloadCount   = mysql_result($result3,0,"DownloadCount");	
							$this_day_IpCount   = mysql_result($result3,0,"IpCount");
							
							$day_RequestCount = 
							$day_UploadCount = 
							$day_DownloadCount =
							$day_IpCount = 
							
							if($this_day_StartTime == $nStartTime)
							{
								$day_RequestCount = $RequestCount;
								$day_UploadCount = $UploadCount;
								$day_DownloadCount = $DownloadCount;
								$day_IpCount = $IpCount;
							}
							else
							{
								$day_RequestCount = $this_day_RequestCount+$RequestCount;
								$day_UploadCount = $this_day_UploadCount+$UploadCount;
								$day_DownloadCount = $this_day_DownloadCount+$DownloadCount;
								$day_IpCount = $this_day_IpCount+$IpCount;
							}						
						}
						else
						{
							$sql ="INSERT INTO domain_stat_day(id,group_id,buy_id,node_id,time,StartTime,EndTime,Host,RequestCount,UploadCount,DownloadCount,IpCount) 
								VALUES(NULL,$groupid,0,$node_id,$nDayTime,$nStartTime,$nEndTime,'$sHost',$RequestCount,$UploadCount,$DownloadCount,$IpCount)";
							$result4 = mysql_query($sql,$db_link);
							if(!$result4)
							{
								echo mysql_error($db_link);
							}	
						}
						*/						
						/*
						$sql = "INSERT INTO domain_stat(id,group_id,node_id,time,NO,ProxyID,Host,Balance,Enable,StartTime,EndTime,RequestCount,
								UploadCount,DownloadCount,IpCount,bandwidth_down,bandwidth_up,down_increase,up_increase) 
							VALUE(NULL,'$groupid','$node_id','$nTime','$nNo','$nProxyID','$sHost','$nBalance','$bEnable','$nStartTime','$nEndTime',
								'$RequestCount','$UploadCount','$DownloadCount','$IpCount','$bandwidth_down','$bandwidth_up',$down_increase,$up_increase);";
							
						//echo $sql.'<br /><br />';
						if(!mysql_query($sql,$db_link))
						{
							echo mysql_error().'<br />'.$sql.'<br />';
						}
						*/
					}
				}		
			}
			
			$aryBuySum = array();
			
			// 汇总并计算每个域名的带宽
			$sql = "select * from fikcdn_domain";
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count = mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$this_domain_id	 = mysql_result($result,$i,"id");
					$this_hostname	 = mysql_result($result,$i,"hostname");
					$this_upstream	 = mysql_result($result,$i,"upstream");
					$this_group_id	 = mysql_result($result,$i,"group_id");
					$this_buy_id	 = mysql_result($result,$i,"buy_id");
					$this_status	 = mysql_result($result,$i,"status");
					
					$down_bandwidth =0;
					$up_bandwidth =0;
					$SumRequestCount=0;
					$SumIpCount=0;
					$SumDownloadCount=0;
					$SumUploadCount=0;
					
					/*
					$sql = "select sum(bandwidth_down),sum(bandwidth_up) from domain_stat where Host='$this_hostname' AND group_id='$this_group_id'";
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$down_bandwidth	 = mysql_result($result2,0,"sum(bandwidth_down)");
						$up_bandwidth	 = mysql_result($result2,0,"sum(bandwidth_up)");
					}	
					*/
					
					$down_bandwidth = $aryHostSum[$this_group_id][$this_hostname]["bandwidth_down"];
					$up_bandwidth = $aryHostSum[$this_group_id][$this_hostname]["bandwidth_up"];
					$SumRequestCount = $aryHostSum[$this_group_id][$this_hostname]["RequestCount"];
					$SumIpCount = $aryHostSum[$this_group_id][$this_hostname]["IpCount"];
					$SumDownloadCount = $aryHostSum[$this_group_id][$this_hostname]["DownloadCount"];
					$SumUploadCount = $aryHostSum[$this_group_id][$this_hostname]["UploadCount"];
					
					if(strlen($down_bandwidth)<=0) $down_bandwidth=0;
					if(strlen($up_bandwidth)<=0) $up_bandwidth=0;
					if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
					if(strlen($SumIpCount)<=0) $SumIpCount=0;
					if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
					if(strlen($SumUploadCount)<=0) $SumUploadCount=0;	
					
					$nBWOffset = 1.1;
						
					// MB
					$SumDownloadCount = round((($SumDownloadCount*$nBWOffset)/(1024*1024)),2);
					$SumUploadCount = round((($SumUploadCount*$nBWOffset)/(1024*1024)),2);
					$SumRequestCount = round($SumRequestCount*1.01,0);
					
					/*
					if(date("H")>18 && date("H")<23)
					{
						$nBWOffset = 1.2;
					}
					*/
					
					//修正域名带宽值 MB
					$down_bandwidth = round($down_bandwidth*$nBWOffset,2);
					$up_bandwidth = round($up_bandwidth*$nBWOffset,2);		
					
					// 汇总套餐数据
					if(strlen($aryBuySum[$this_buy_id]["bandwidth_down"])<=0){
						$aryBuySum[$this_buy_id]["bandwidth_down"] = $down_bandwidth;
					}else{
						$aryBuySum[$this_buy_id]["bandwidth_down"] += $down_bandwidth;
					}
						
					if(strlen($aryBuySum[$this_buy_id]["bandwidth_up"])<=0){
						$aryBuySum[$this_buy_id]["bandwidth_up"] = $up_bandwidth;
					}else{
						$aryBuySum[$this_buy_id]["bandwidth_up"] += $up_bandwidth;
					}
					
					if(strlen($aryBuySum[$this_buy_id]["RequestCount"])<=0){
						$aryBuySum[$this_buy_id]["RequestCount"] = $SumRequestCount;
					}else{
						$aryBuySum[$this_buy_id]["RequestCount"] += $SumRequestCount;
					}	
					
					if(strlen($aryBuySum[$this_buy_id]["IpCount"])<=0){
						$aryBuySum[$this_buy_id]["IpCount"] = $SumIpCount;
					}else{
						$aryBuySum[$this_buy_id]["IpCount"] += $SumIpCount;
					}
					
					if(strlen($aryBuySum[$this_buy_id]["DownloadCount"])<=0){
						$aryBuySum[$this_buy_id]["DownloadCount"] = $SumDownloadCount;
					}else{
						$aryBuySum[$this_buy_id]["DownloadCount"] += $SumDownloadCount;
					}
					
					if(strlen($aryBuySum[$this_buy_id]["UploadCount"])<=0){
						$aryBuySum[$this_buy_id]["UploadCount"] = $SumUploadCount;
					}else{
						$aryBuySum[$this_buy_id]["UploadCount"] += $SumUploadCount;
					}																	
											
					$sql ="INSERT INTO domain_stat_host_bandwidth(id,group_id,buy_id,domain_id,time,Host,down_increase,up_increase,bandwidth_down,bandwidth_up,RequestCount_increase,IpCount_increase) 
							VALUE(NULL,$this_group_id,$this_buy_id,$this_domain_id,$nTime,'$this_hostname',$SumDownloadCount,$SumUploadCount,$down_bandwidth,$up_bandwidth,$SumRequestCount,$SumIpCount)";	
					echo $sql.'<br /><br />';						
					$result2 = mysql_query($sql,$db_link);
					if(!$result2)
					{
						echo "INSERT ".mysql_error().'.sql =   '.$sql.'<br /><br />';
					}
					
					/*
					$sql = "SELECT * FROM domain_stat_group_day WHERE time=$nDayTime AND domain_id=$this_domain_id AND Host='$this_hostname'";
					$result3 = mysql_query($sql,$db_link);
					if($result3 && mysql_num_rows($result3)>0)			
					{							
						//查询按天统计的
						$sql = "UPDATE domain_stat_group_day SET RequestCount=RequestCount+$SumRequestCount,UploadCount=UploadCount+$SumUploadCount,DownloadCount=DownloadCount+$SumDownloadCount,IpCount=IpCount+$SumIpCount 
										WHERE time=$nDayTime AND domain_id=$this_domain_id AND Host='$this_hostname'";
						$result3 = mysql_query($sql,$db_link);
						if($result3)
						{
							echo 'UPDATE domain_stat_group_day ok.<br /><br />';
							//$count_summm = mysql_num_rows($result3);
							//echo mysql_error($db_link).'  ,,,,count_summm='.$count_summm.'<br /><br />';
							//echo $sql.'   condddd='.$condddd.'====OK!!!<br /><br />';				
						}
						else
						{
							echo "UPDATE domain_stat_group_day error,". mysql_error($db_link);						
						}
					}
					else
					{
						//echo mysql_error($db_link).'sql='.$sql.'====Failed!!!<br /><br />';			
						//echo 'INSERT <br /><br />';		
						
						$sql = "INSERT INTO domain_stat_group_day(id,group_id,buy_id,domain_id,time,Host,RequestCount,UploadCount,DownloadCount,IpCount) 
							VALUES(NULL,'$this_group_id','$this_buy_id','$this_domain_id','$nDayTime','$this_hostname',$SumRequestCount,$SumUploadCount,$SumDownloadCount,$SumIpCount)";
						$result3 = mysql_query($sql,$db_link);	
						if(!$result3)
						{	
							echo $sql.'<br />';
							echo "INSERT domain_stat_group_day error,". mysql_error($db_link).'<br />';
						}	
						else
						{
							echo $sql.'<br />';
						}				
					}	
					*/									
				}
			}
			
			// 汇总并计算产品套餐的带宽和流量
			$sql = "select * from fikcdn_buy";
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count = mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$this_buy_id	 = mysql_result($result,$i,"id");
					
					$down_bandwidth =0;
					$up_bandwidth =0;
					$SumRequestCount=0;
					$SumIpCount=0;
					$SumDownloadCount=0;
					$SumUploadCount=0;
					
					// MB				
					$down_bandwidth = $aryBuySum[$this_buy_id]["bandwidth_down"];
					$up_bandwidth = $aryBuySum[$this_buy_id]["bandwidth_up"];
					$SumRequestCount = $aryBuySum[$this_buy_id]["RequestCount"];
					$SumIpCount = $aryBuySum[$this_buy_id]["IpCount"];
					$SumDownloadCount = $aryBuySum[$this_buy_id]["DownloadCount"];
					$SumUploadCount = $aryBuySum[$this_buy_id]["UploadCount"];
					
					if(strlen($down_bandwidth)<=0) $down_bandwidth=0;
					if(strlen($up_bandwidth)<=0) $up_bandwidth=0;
					if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
					if(strlen($SumIpCount)<=0) $SumIpCount=0;
					if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
					if(strlen($SumUploadCount)<=0) $SumUploadCount=0;		
					
					$sql ="INSERT INTO domain_stat_product_bandwidth(id,buy_id,time,down_increase,up_increase,bandwidth_down,bandwidth_up,RequestCount_increase) 
							VALUE(NULL,$this_buy_id,$nTime,$SumDownloadCount,$SumUploadCount,$down_bandwidth,$up_bandwidth,$SumRequestCount)";	
					echo $sql.'<br /><br />';							
					$result2 = mysql_query($sql,$db_link);
					if(!$result2)
					{
						echo "INSERT ".mysql_error().'.sql =   '.$sql.'<br /><br />';
					}									
				}			
			}			
			
			$timenow = time();
			$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='proxy_list_enter'";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				echo "UPDATE fikcdn_params error,".mysql_error($db_link);
			}							
			
			mysql_close($db_link);			
		}
	}
	
	
	printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
	echo "<br /><br />";
}

$timeval1 = mktime(0,0,0,date("m",time()),date("d",time())-1,date("Y",time()));
echo date("Y-m-d H:i:s",$timeval1)."<br/>";

$timeval1 +=  (60*60*24);
echo date("Y-m-d H:i:s",$timeval1)."<br/>";

$end_time = time();
echo '<br />execute timeval: '.($end_time-$begin_time);
echo "<br /><br /><br /><br /><br />";
	
?>
