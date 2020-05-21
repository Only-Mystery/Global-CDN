<?php
ignore_user_abort();
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
		
		$aryFikStat = array();
				
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
			
			$exetime=0;
			
			while(1)
			{	
				$total_down_increase =0;
				$total_up_increase =0;
				$total_bandwidth_down=0;
				$total_bandwidth_up=0;
				
				$total_upstream_down_increase =0;
				$total_upstream_up_increase =0;
				$total_upstream_bandwidth_down=0;
				$total_upstream_bandwidth_up=0;	
				
				if($exetime==0)
				{
					$exetime = time();
				}
				else
				{
					$timenow2= time();
					if( ($timenow2-$exetime) <= 60*2 )
					{
						sleep(2);
						continue;
					}
					
					$exetime = $timenow2;
				}
					
				//查找所有开放的节点
				$sql = "SELECT * FROM fikcdn_node WHERE is_close=0 $sql_and";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{					
					$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='realtime_list_enter'";
					$result = mysql_query($sql,$db_link);	
					
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
					
					$bStatOk = false;
					
					if($aryFikResult["Return"]=="True")
					{
						$StartTime= $aryFikResult['StartTime'];
						$EndTime= $aryFikResult['CurrentTime'];
						$CurrentUserConnections = $aryFikResult['CurrentUserConnections'];
						$CurrentUpstreamConnections = $aryFikResult['CurrentUpstreamConnections'];
						$TotalSendKB = $aryFikResult['TotalSendKB'];
						$TotalRecvKB = $aryFikResult['TotalRecvKB'];
						$TotalSendToResponseKB = $aryFikResult['TotalSendToResponseKB'];
						$TotalRecvFromResponseKB = $aryFikResult['TotalRecvFromResponseKB'];
						$CurrentTickCount = $aryFikResult['CurrentTickCount'];
						
						$nStartTime = strtotime($StartTime);
						$nEndTime = strtotime($EndTime);
						
						if( strlen($aryFikStat[$node_id]['StartTime'])>0)
						{
							if($aryFikStat[$node_id]['StartTime'] == $StartTime)
							{
								$timeval = $CurrentTickCount - $aryFikStat[$node_id]['CurrentTickCount'];
								if($timeval>0)
								{
									$down_increase = $TotalSendKB - $aryFikStat[$node_id]['TotalSendKB'];
									$up_increase = $TotalRecvKB - $aryFikStat[$node_id]['TotalRecvKB'];														                                	$upstream_down_increase = $TotalSendToResponseKB - $aryFikStat[$node_id]['TotalSendToResponseKB'];
									$upstream_up_increase = $TotalRecvFromResponseKB - $aryFikStat[$node_id]['TotalRecvFromResponseKB'];
									
									$bandwidth_down = ((($down_increase*8)/1024)/$timeval)*1000;
									$bandwidth_up = ((($up_increase*8)/1024)/$timeval)*1000;
									$upstream_bandwidth_down =((($upstream_down_increase*8)/1024)/$timeval)*1000;
									$upstream_bandwidth_up =((($upstream_up_increase*8)/1024)/$timeval)*1000;
									
									$bStatOk = true;
								}
							}
							else
							{
								if( ($nEndTime - $nStartTime) <= 15*60)
								{
									$timeval = $nEndTime - $nStartTime;			
									if($timeval>0)
									{										
										$down_increase = $TotalSendKB;
										$up_increase = $TotalRecvKB;												                                						                               	 		$upstream_down_increase = $TotalSendToResponseKB;
										$upstream_up_increase = $TotalRecvFromResponseKB;
										
										$bandwidth_down = ((($down_increase*8)/1024)/$timeval)*1000;
										$bandwidth_up = ((($up_increase*8)/1024)/$timeval)*1000;
										$upstream_bandwidth_down =((($upstream_down_increase*8)/1024)/$timeval)*1000;
										$upstream_bandwidth_up =((($upstream_up_increase*8)/1024)/$timeval)*1000;
										
										$bStatOk = true;
									}
								}
							}
					
							$aryFikStat[$node_id]['StartTime'] = $StartTime;
							$aryFikStat[$node_id]['CurrentTime'] = $EndTime;
							$aryFikStat[$node_id]['CurrentUserConnections'] = $CurrentUpstreamConnections;
							$aryFikStat[$node_id]['CurrentUpstreamConnections'] = $CurrentUpstreamConnections;
							$aryFikStat[$node_id]['TotalSendKB'] = $TotalSendKB;
							$aryFikStat[$node_id]['TotalRecvKB'] = $TotalRecvKB;
							$aryFikStat[$node_id]['TotalSendToResponseKB'] = $TotalSendToResponseKB;
							$aryFikStat[$node_id]['TotalRecvFromResponseKB'] = $TotalRecvFromResponseKB;
							$aryFikStat[$node_id]['CurrentTickCount'] = $CurrentTickCount;		
						}
						
						if($bStatOk==false)
						{
							continue;
						}
						
						$bandwidth_down = round($bandwidth_down,2);
						$bandwidth_up = round($bandwidth_up,2);
						if(strlen($down_increase)<=0) $down_increase=0;
						if(strlen($up_increase)<=0) $up_increase=0;
						
						$upstream_bandwidth_down = round($upstream_bandwidth_down,2);
						$upstream_bandwidth_up = round($upstream_bandwidth_up,2);
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
					
						$sql = "INSERT INTO realtime_list(id,group_id,node_id,time,StartTime,EndTime,CurrentUserConnections,CurrentUpstreamConnections,TotalSendKB,TotalRecvKB,					TotalSendToResponseKB,TotalRecvFromResponseKB,bandwidth_down,bandwidth_up,down_increase,up_increase,upstream_bandwidth_down,upstream_bandwidth_up,upstream_down_increase,upstream_up_increase) VALUE(NULL,'$groupid','$node_id','$nTime',$nStartTime,$nEndTime,'$CurrentUserConnections','$CurrentUpstreamConnections','$TotalSendKB','$TotalRecvKB','$TotalSendToResponseKB','$TotalRecvFromResponseKB','$bandwidth_down','$bandwidth_up','$down_increase','$up_increase','$upstream_bandwidth_down','$upstream_bandwidth_up','$upstream_down_increase','$upstream_up_increase')" ;
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
				$sql = "INSERT INTO realtime_list_all(id,time,bandwidth_down,bandwidth_up,user_down,user_up,upstream_bandwidth_down,upstream_bandwidth_up,upstream_down,upstream_up) 					VALUES(NULL,'$nTime','$total_bandwidth_down','$total_bandwidth_up','$total_down_increase','$total_up_increase','$total_upstream_bandwidth_down','$total_upstream_bandwidth_up','$total_upstream_down_increase','$total_upstream_up_increase')";
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
			}
		}
	}
}
	
?>
