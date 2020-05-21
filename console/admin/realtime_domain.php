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

if($sMod=="proxy")
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
				
			$exetime=0;
			$aryDomain = array();
			
			while(1)
			{				
				if($exetime==0)
				{
					$exetime = time();
				}
				else
				{
					$timenow2= time();
					if( ($timenow2-$exetime) <= 60*3 )
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
					$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='proxy_list_enter'";
					$result = mysql_query($sql,$db_link);	
					
					$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrQuery);
					PubFunc_EchoJsonAndExit($aryResult,$db_link);
				}
	
				$nTime = mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y"));
				$nDayTime = mktime(0,0,0,date("m"),date("d"),date("Y"));
				
				echo "$nTime=".date("Y-m-d H:i:s",$nTime)."<br/>";
				echo "$nDayTime=".date("Y-m-d H:i:s",$nDayTime)."<br/>";
				echo "<br>sql=".$sql.'<br />';
	
				$aryHostSum = array();
				
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
					
					$aryFikResult = FikApi_ProxyList($sFikIP,$admin_port,$SessionID);
					if($aryFikResult == false){
						$aryFikResult = FikApi_ProxyList($sFikIP,$admin_port,$SessionID);
						if($aryFikResult==false){
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
					
					$bStatOk = false; 
					
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
							$CurrentTickCount = $aryFikResult["Lists"][$k]["CurrentTickCount"];
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
							
							if( strlen($aryDomain[$node_id][$sHost]["StartTime"])>0)
							{
								if( strlen($aryDomain[$node_id][$sHost]["StartTime"])==$StartTime)
								{
									$timeval = $CurrentTickCount - $aryDomain[$node_id][$sHost]["CurrentTickCount"];
									if($timeval>0)
									{
										$down_increase =$DownloadCount-$aryDomain[$node_id][$sHost]["DownloadCount"];
										$up_increase=$UploadCount-$aryDomain[$node_id][$sHost]["UploadCount"];;
										$RequestCount_increase =$RequestCount-$aryDomain[$node_id][$sHost]["RequestCount"];;
										$IpCount_increase =$IpCount-$aryDomain[$node_id][$sHost]["IpCount"];;
										
										$bandwidth_down = (($down_increase*8)/$timeval)*1000;
										$bandwidth_up = (($up_increase*8)/$timeval)*1000;						
										
										$bandwidth_down = round(($bandwidth_down)/(1024*1024),2);
										$bandwidth_up = round(($bandwidth_up)/(1024*1024),2);
										
										$bStatOk = true;
									}
								}
								else
								{
									if( ($nEndTime - $nStartTime) < 15*60)
									{
										$timeval = $nEndTime - $nStartTime;
										if($timeval>0)
										{
											$down_increase =$DownloadCount;
											$up_increase=$UploadCount;
											$RequestCount_increase =$RequestCount;
											$IpCount_increase =$IpCount;
											
											$bandwidth_down = (($down_increase*8)/$timeval);
											$bandwidth_up = (($up_increase*8)/$timeval);						
											
											$bandwidth_down = round(($bandwidth_down)/(1024*1024),2);
											$bandwidth_up = round(($bandwidth_up)/(1024*1024),2);
											
											$bStatOk = true;
										}
									}
								}
							}																	
							
							$aryDomain[$node_id][$sHost]["No"] = $nNo;
							$aryDomain[$node_id][$sHost]["ProxyID"] = $nProxyID;
							$aryDomain[$node_id][$sHost]["Host"] = $sHost;
							$aryDomain[$node_id][$sHost]["Balance"] = $nBalance;
							$aryDomain[$node_id][$sHost]["Enable"] = $bEnable;
							$aryDomain[$node_id][$sHost]["StartTime"] = $StartTime;
							$aryDomain[$node_id][$sHost]["EndTime"] = $EndTime;
							$aryDomain[$node_id][$sHost]["RequestCount"] = $RequestCount;
							$aryDomain[$node_id][$sHost]["UploadCount"] = $UploadCount;
							$aryDomain[$node_id][$sHost]["DownloadCount"] = $DownloadCount;
							$aryDomain[$node_id][$sHost]["IpCount"] = $IpCount;
							$aryDomain[$node_id][$sHost]["UserConnections"] = $UserConnections;
							$aryDomain[$node_id][$sHost]["UpstreamConnections"] = $UpstreamConnections;
							$aryDomain[$node_id][$sHost]["CurrentTickCount"] = $CurrentTickCount;
							
							if(strlen($nStartTime)<=0) $nStartTime=0;
							if(strlen($nEndTime)<=0) $nEndTime=0;
							
							if(strlen($down_increase)<=0) $down_increase=0;
							if(strlen($up_increase)<=0) $up_increase=0;
							
							if($bStatOk)
							{
								if(strlen($aryHostSum[$groupid][$sHost]["bandwidth_down"])<=0){
									$aryHostSum[$groupid][$sHost]["bandwidth_down"]=$bandwidth_down;
								}else{
									$aryHostSum[$groupid][$sHost]["bandwidth_down"] += $bandwidth_down;
								}
								
								if(strlen($aryHostSum[$groupid][$sHost]["bandwidth_up"])<=0){
									$aryHostSum[$groupid][$sHost]["bandwidth_up"]=$bandwidth_up;
								}else{
									$aryHostSum[$groupid][$sHost]["bandwidth_up"] += $bandwidth_up;
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
							}
						}//for
					}//true		
				}//for
				
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
												
						$sql ="INSERT INTO domain_stat_host_bandwidth(id,group_id,buy_id,domain_id,time,Host,down_increase,up_increase,bandwidth_down,bandwidth_up,RequestCount_increase,IpCount_increase) 			VALUE(NULL,$this_group_id,$this_buy_id,$this_domain_id,$nTime,'$this_hostname',$SumDownloadCount,$SumUploadCount,$down_bandwidth,$up_bandwidth,$SumRequestCount,$SumIpCount)";	
						echo $sql.'<br /><br />';						
						$result2 = mysql_query($sql,$db_link);
						if(!$result2)
						{
							echo "INSERT ".mysql_error().'.sql =   '.$sql.'<br /><br />';
						}							
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
