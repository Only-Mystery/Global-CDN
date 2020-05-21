<?php
//统计汇总实时数据
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
	
$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
$sCron  	= isset($_GET['cron'])?$_GET['cron']:'';
if($sCron!="fik")
{
	exit();
}

if($sMod=="collect")
{
	if($sAction=="product_day")
	{
		$nGetTimeval = isset($_GET['timeval'])?$_GET['timeval']:'';
		if(strlen($nTimeval)>0 )
		{
			if(!is_numeric($nGetTimeval))
			{
				exit();
			}
		}
		else
		{
			$nGetTimeval = time()-(60*60*24);
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{	
			exit();
		}
		
		//删除数据
		$timeval1 = time()-(1*60*60*24);
		$sql = "DELETE FROM domain_stat WHERE time<$timeval1";
		echo $sql.'<br />';
		//$result = mysql_query($sql,$db_link);
		
		//删除数据
		$timeval1 = time()-(3*60*60*24);
		$sql = "DELETE FROM realtime_list WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
				
		//删除数据
		$sql = "DELETE FROM realtime_totalstat WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
				
		//删除数据
		$timeval1 = time()-(120*60*60*24);
		$sql = "DELETE FROM realtime_list_max WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);	
		
		//删除数据
		$timeval1 = time()-(180*60*60*24);
		$sql = "DELETE FROM realtime_list_day WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);		
		
		//删除数据
		$timeval1 = time()-(365*3*60*60*24);
		$sql = "DELETE FROM realtime_list_all_host WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);	
							
		//删除数据
		$timeval1 = time()-(365*5*60*60*24);
		$sql = "DELETE FROM realtime_list_all_day WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);	
		
		//删除数据
		$timeval1 = time()-(90*60*60*24);
		$sql = "DELETE FROM realtime_list_all WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);	
						
		//删除数据
		$timeval1 = time()-(3*60*60*24);
		$sql = "DELETE FROM domain_stat_host_bandwidth WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
		
		//删除数据
		$timeval1 = time()-(60*60*60*24);
		$sql = "DELETE FROM domain_stat_host_max_bandwidth WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
				
		//删除数据
		$timeval1 = time()-(120*60*60*24);
		$sql = "DELETE FROM domain_stat_group_day WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
				
		//删除数据
		$timeval1 = time()-(3*60*60*24);
		$sql = "DELETE FROM domain_stat_product_bandwidth WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
						
		//删除数据
		$timeval1 = time()-(120*60*60*24);
		$sql = "DELETE FROM domain_stat_product_day WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
		
		//删除数据
		$timeval1 = time()-(120*60*60*24);
		$sql = "DELETE FROM domain_stat_product_max_bandwidth WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);	
								
		$timeval1 = mktime(0,0,0,date("m",$nGetTimeval),date("d",$nGetTimeval),date("Y",$nGetTimeval));
		$timeval2 = $timeval1+(60*60*24);
		
		echo "$timeval1=".date("Y-m-d H:i:s",$timeval1)."<br/>";
		echo "$timeval2=".date("Y-m-d H:i:s",$timeval2)."<br/>";
		
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
				
				$SumUploadCount =0;
				$SumDownloadCount =0;
				$SumRequestCount =0;
				$SumIpCount =0;				
												
				//统计当天某个套餐发送数据的总数
				$sql = "SELECT sum(down_increase),sum(up_increase),sum(RequestCount_increase) FROM domain_stat_host_max_bandwidth WHERE domain_id=$this_domain_id AND time>='$timeval1' AND time<'$timeval2'";		
				//echo $sql.'<br /><br />';			
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$SumDownloadCount = mysql_result($result2,0,"sum(down_increase)");
					$SumUploadCount	= mysql_result($result2,0,"sum(up_increase)");
					$SumRequestCount = mysql_result($result2,0,"sum(RequestCount_increase)");		
				}			
				
				$max_bandwidth_down = 0;
				$time_for_max = 0;
				$bandwidth_down_for_max=0;
				$bandwidth_up_for_max = 0;
								
				//统计当天某个域名的峰值带宽发生的时间和值
				$sql = "SELECT max(bandwidth_down) FROM domain_stat_host_max_bandwidth WHERE domain_id=$this_domain_id AND time>='$timeval1' AND time<'$timeval2'";		
				//echo $sql.'<br /><br />';			
				$result3 = mysql_query($sql,$db_link);
				if($result3 && mysql_num_rows($result3)>0)
				{
					$max_bandwidth_down = mysql_result($result3,0,"max(bandwidth_down)");	
					$sql = "select * from domain_stat_host_max_bandwidth where domain_id=$this_domain_id AND time>='$timeval1' AND time<'$timeval2' AND bandwidth_down='$max_bandwidth_down'";
					$result4 = mysql_query($sql,$db_link);
					if($result4 && mysql_num_rows($result4)>0)
					{
						$id_for_max = mysql_result($result4,0,"id");
						$time_for_max = mysql_result($result4,0,"time");
						$bandwidth_down_for_max = mysql_result($result4,0,"bandwidth_down");
						$bandwidth_up_for_max = mysql_result($result4,0,"bandwidth_up");
					}
				}	
				
				//MB
				if(strlen($SumUploadCount)<=0) $SumUploadCount=0;
				if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
				if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
				if(strlen($SumIpCount)<=0) $SumIpCount=0;
								
				// 删除已有的
				$sql = "DELETE FROM domain_stat_group_day WHERE time=$timeval1 AND domain_id='$this_domain_id'";
				$result3 = mysql_query($sql,$db_link);			
				
				$sql = "INSERT INTO domain_stat_group_day(id,group_id,buy_id,domain_id,Host,time,RequestCount,UploadCount,DownloadCount,IpCount,time_for_max,bandwidth_down,bandwidth_up) VALUES(NULL,$this_group_id,$this_buy_id,$this_domain_id,'$this_hostname',$timeval1,'$SumRequestCount','$SumUploadCount','$SumDownloadCount','$SumIpCount','$time_for_max','$bandwidth_down_for_max','$bandwidth_up_for_max')";
				$result3 = mysql_query($sql,$db_link);	
				if(!$result3)
				{
					echo mysql_error($db_link);
				}				
			}
		}

		$sql = "SELECT * FROM fikcdn_buy";
		$result = mysql_query($sql,$db_link);
		if($result)
		{	
			$row_count = mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_buy_id	 = mysql_result($result,$i,"id");
				$this_product_id = mysql_result($result,$i,"product_id");
	
				$SumRequestCount=0;
				$SumUploadCount=0;
				$SumDownloadCount=0;
				$SumIpCount=0;
				
				//统计当天某个套餐发送数据的总数
				$sql = "SELECT sum(RequestCount),sum(UploadCount),sum(DownloadCount),sum(IpCount) FROM domain_stat_group_day WHERE buy_id='$this_buy_id' AND time='$timeval1'";		
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
					$SumUploadCount	= mysql_result($result2,0,"sum(UploadCount)");
					$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
					$SumIpCount	= mysql_result($result2,0,"sum(IpCount)");		
				}
				
				$max_bandwidth_down = 0;
				$time_for_max = 0;
				$bandwidth_down_for_max=0;
				$bandwidth_up_for_max = 0;
								
				//统计当天某个域名的峰值带宽发生的时间和值
				$sql = "SELECT max(bandwidth_down) FROM domain_stat_product_max_bandwidth WHERE buy_id=$this_buy_id AND time>='$timeval1' AND time<'$timeval2'";		
				//echo $sql.'<br /><br />';			
				$result3 = mysql_query($sql,$db_link);
				if($result3 && mysql_num_rows($result3)>0)
				{
					$max_bandwidth_down = mysql_result($result3,0,"max(bandwidth_down)");	
					$sql = "select * from domain_stat_product_max_bandwidth where buy_id=$this_buy_id AND time>='$timeval1' AND time<'$timeval2' AND bandwidth_down='$max_bandwidth_down'";
					$result4 = mysql_query($sql,$db_link);
					if($result4 && mysql_num_rows($result4)>0)
					{
						$id_for_max = mysql_result($result4,0,"id");
						$time_for_max = mysql_result($result4,0,"time");
						$bandwidth_down_for_max = mysql_result($result4,0,"bandwidth_down");
						$bandwidth_up_for_max = mysql_result($result4,0,"bandwidth_up");
					}
				}	
								
				if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
				if(strlen($SumUploadCount)<=0) $SumUploadCount=0;
				if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
				if(strlen($SumIpCount)<=0) $SumIpCount=0;
				
				//GB
				$SumUploadCount2 = round(($SumUploadCount/(1024)),2);
				$SumDownloadCount2 = round(($SumDownloadCount/(1024)),2);
				
				// 删除已有的
				$sql = "DELETE FROM domain_stat_product_day WHERE time=$timeval1 AND buy_id='$this_buy_id'";
				$result3 = mysql_query($sql,$db_link);					
	
				$sql = "INSERT INTO domain_stat_product_day(id,buy_id,time,RequestCount,UploadCount,DownloadCount,IpCount,time_for_max,bandwidth_down,bandwidth_up) VALUES(NULL,$this_buy_id,$timeval1,'$SumRequestCount','$SumUploadCount2','$SumDownloadCount2','$SumIpCount','$time_for_max','$bandwidth_down_for_max','$bandwidth_up_for_max')";

				$result3 = mysql_query($sql,$db_link);	
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}
		}
		
		// 统计节点的一天的流量
		$sql = "select * from fikcdn_node";
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$sum_down_increase =0;
			$sum_up_increase =0;
			$sum_upstream_down_increase =0;
			$sum_upstream_up_increase =0;
					
			$row_count = mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	 = mysql_result($result,$i,"id");
				$this_group_id	 = mysql_result($result,$i,"groupid");
				
				//节点的流量 KB
				$sql = "SELECT sum(down_increase),sum(up_increase),sum(upstream_down_increase),sum(upstream_up_increase) FROM realtime_list_max WHERE node_id='$this_node_id' AND time>='$timeval1' AND time<'$timeval2'";
				$result2 = mysql_query($sql,$db_link);	
				if($result2 && mysql_num_rows($result2)>0)
				{
					$sum_down_increase	 = mysql_result($result2,0,"sum(down_increase)");
					$sum_up_increase = mysql_result($result2,0,"sum(up_increase)");
					$sum_upstream_down_increase = mysql_result($result2,0,"sum(upstream_down_increase)");
					$sum_upstream_up_increase = mysql_result($result2,0,"sum(upstream_up_increase)");
				}
				
				$max_bandwidth_down = 0;
				$time_for_max = 0;
				$bandwidth_down_for_max=0;
				$bandwidth_up_for_max = 0;
								
				//统计当天某个域名的峰值带宽发生的时间和值
				$sql = "SELECT max(bandwidth_down) FROM realtime_list_max WHERE node_id=$this_node_id AND time>='$timeval1' AND time<'$timeval2'";		
				//echo $sql.'<br /><br />';			
				$result3 = mysql_query($sql,$db_link);
				if($result3 && mysql_num_rows($result3)>0)
				{
					$max_bandwidth_down = mysql_result($result3,0,"max(bandwidth_down)");	
					$sql = "select * from realtime_list_max where node_id=$this_node_id AND time>='$timeval1' AND time<'$timeval2' AND bandwidth_down='$max_bandwidth_down'";
					$result4 = mysql_query($sql,$db_link);
					if($result4 && mysql_num_rows($result4)>0)
					{
						$id_for_max = mysql_result($result4,0,"id");
						$time_for_max = mysql_result($result4,0,"time");
						$bandwidth_down_for_max = mysql_result($result4,0,"bandwidth_down");
						$bandwidth_up_for_max = mysql_result($result4,0,"bandwidth_up");
					}
				}	
								
				// 删除已有的
				$sql = "DELETE FROM realtime_list_day WHERE time='$timeval1' AND node_id='$this_node_id'";
				$result3 = mysql_query($sql,$db_link);	
				
				$sql = "INSERT INTO realtime_list_day(id,node_id,group_id,time,user_down,user_up,upstream_down,upstream_up,time_for_max,bandwidth_down,bandwidth_up) VALUES(NULL,'$this_node_id','$this_group_id','$timeval1','$sum_down_increase','$sum_up_increase','$sum_upstream_down_increase','$sum_upstream_up_increase','$time_for_max','$bandwidth_down_for_max','$bandwidth_up_for_max')";
				$result3 = mysql_query($sql,$db_link);
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}
		}	
			
		//统计所有节点一天的流量
		$sum_down_increase =0;
		$sum_up_increase =0;
		$sum_upstream_down_increase =0;
		$sum_upstream_up_increase =0;
											
		//所有节点的流量 MB
		$sql = "SELECT sum(user_down),sum(user_up),sum(upstream_down),sum(upstream_up) FROM realtime_list_all_host WHERE time>='$timeval1' AND time<$timeval2";
		$result2 = mysql_query($sql,$db_link);	
		if($result2 && mysql_num_rows($result2)>0)
		{
			$sum_down_increase	 = mysql_result($result2,0,"sum(user_down)");
			$sum_up_increase = mysql_result($result2,0,"sum(user_up)");
			$sum_upstream_down_increase = mysql_result($result2,0,"sum(upstream_down)");
			$sum_upstream_up_increase = mysql_result($result2,0,"sum(upstream_up)");
		}
				
		// MB				
		if(strlen($sum_down_increase)<=0) $sum_down_increase=0;	
		if(strlen($sum_up_increase)<=0) $sum_up_increase=0;	
		if(strlen($sum_upstream_down_increase)<=0) $sum_upstream_down_increase=0;	
		if(strlen($sum_upstream_up_increase)<=0) $sum_upstream_up_increase=0;	
			
		// 删除已有的
		$sql = "DELETE FROM realtime_list_all_day WHERE time=$timeval1";
		$result3 = mysql_query($sql,$db_link);	
				
		$sql = "INSERT INTO realtime_list_all_day(id,time,user_down,user_up,upstream_down,upstream_up) 
				VALUES(NULL,'$timeval1','$sum_down_increase','$sum_up_increase','$sum_upstream_down_increase','$sum_upstream_up_increase')";
		$result3 = mysql_query($sql,$db_link);	
		if(!$result3)
		{
			echo mysql_error($db_link);
		}					
	}	
	else if($sAction=="product_month")
	{
		$nGetTimeval = isset($_GET['timeval'])?$_GET['timeval']:'';
		if(strlen($nTimeval)>0 )
		{
			if(!is_numeric($nGetTimeval))
			{
				exit();
			}
		}
		else
		{
			$nGetTimeval = time()-(60*60*24);
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{	
			exit();
		}
		
		//删除数据
		$timeval1 = time()-(500*60*60*24);
		$sql = "DELETE FROM domain_stat_month WHERE time<$timeval1";
		echo $sql.'<br />';
		$result = mysql_query($sql,$db_link);
		
		//删除过期的失败任务
		$bAutoDelTask=1;
		$sql = "SELECT * FROM fikcdn_params where name='auto_del_task';";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$bAutoDelTask  = mysql_result($result,0,"value");
		}		
		
		if($bAutoDelTask)
		{
			$TaskTime = time()-10*60*60*24;
			$sql = "DELETE FROM fikcdn_task WHERE time<$TaskTime AND execute_count>=$PubDefine_TaskMaxExecuteCount";
			$result = mysql_query($sql,$db_link);
		}
				
		$timeval1 = mktime(0,0,0,date("m",$nGetTimeval),1,date("Y",$nGetTimeval));
		$timeval2 = mktime(0,0,0,date("m",$nGetTimeval)+1,1,date("Y",$nGetTimeval));
		
		echo "timeval1=".date("Y-m-d H:i:s",$timeval1)."<br/>";
		echo "timeval2=".date("Y-m-d H:i:s",$timeval2)."<br/>";
		echo "nGetTimeval=".date("Y-m-d H:i:s",$nGetTimeval)."<br/>";
		
		$sql = "SELECT * FROM fikcdn_buy";
		$result = mysql_query($sql,$db_link);
		if(!$result)
		{	
			exit();
		}
		
		$timenow = time();
		
		$row_count = mysql_num_rows($result);
		for($i=0;$i<$row_count;$i++)
		{
			$this_buy_id	 = mysql_result($result,$i,"id");
			$this_product_id = mysql_result($result,$i,"product_id");
			$auto_stop_buy = mysql_result($result,$i,"auto_stop");
			$end_time_buy = mysql_result($result,$i,"end_time");
			$data_flow_buy = mysql_result($result,$i,"data_flow");  //MB

			$SumRequestCount=0;
			$SumUploadCount=0;
			$SumDownloadCount=0;
			$SumIpCount=0;
			
			//统计当月某个套餐发送数据的总数
			$sql = "SELECT sum(RequestCount),sum(UploadCount),sum(DownloadCount),sum(IpCount) FROM domain_stat_group_day WHERE buy_id=$this_buy_id AND time>='$timeval1' AND time<$timeval2";		
			$result2 = mysql_query($sql,$db_link);
			if($result2 && mysql_num_rows($result2)>0)
			{
				$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
				$SumUploadCount	= mysql_result($result2,0,"sum(UploadCount)");
				$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
				$SumIpCount	= mysql_result($result2,0,"sum(IpCount)");		
			}
			
			if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
			if(strlen($SumUploadCount)<=0) $SumUploadCount=0;
			if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
			if(strlen($SumIpCount)<=0) $SumIpCount=0;
			
			$SumDownloadCountMB = $SumDownloadCount;
			
			//GB
			$SumUploadCount2 = round(($SumUploadCount/(1024)),2);
			$SumDownloadCount2 = round(($SumDownloadCount/(1024)),2);		
			
			//echo "<br />SumUploadCount2=".$SumUploadCount2;
			//echo "<br />SumDownloadCount2=".$SumDownloadCount2;
			
			/*
			$sql = "INSERT INTO domain_stat_product_month(id,buy_id,time,RequestCount,UploadCount,DownloadCount,IpCount) 
				VALUES(NULL,$this_buy_id,$timeval1,$SumRequestCount,$SumUploadCount,$SumDownloadCount,$SumIpCount) ON DUPLICATE KEY UPDATE RequestCount='$SumRequestCount',UploadCount='$SumUploadCount',DownloadCount='$SumDownloadCount',IpCount='$SumIpCount'";
			$result3 = mysql_query($sql,$db_link);	
			*/	

			$sql = "SELECT * FROM domain_stat_product_month WHERE buy_id=$this_buy_id AND time=$timeval1";
			$result3 = mysql_query($sql,$db_link);
			if(	$result3 && mysql_num_rows($result3)>0)
			{
				$sql = "UPDATE domain_stat_product_month SET RequestCount='$SumRequestCount',UploadCount='$SumUploadCount2',DownloadCount='$SumDownloadCount2',IpCount='$SumIpCount' 
								WHERE buy_id=$this_buy_id AND time=$timeval1";
		
				$result3 = mysql_query($sql,$db_link);
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}
			else
			{			
				$sql = "INSERT INTO domain_stat_product_month(id,buy_id,time,RequestCount,UploadCount,DownloadCount,IpCount) 
								VALUES(NULL,$this_buy_id,$timeval1,'$SumRequestCount','$SumUploadCount2','$SumDownloadCount2','$SumIpCount')";

				$result3 = mysql_query($sql,$db_link);
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}	
			
			//获取累计总流量
			$sql = "SELECT sum(UploadCount),sum(DownloadCount),sum(RequestCount) FROM domain_stat_product_month WHERE buy_id='$this_buy_id'";
			$result3 = mysql_query($sql,$db_link);				
			if($result3 && mysql_num_rows($result3)>0)
			{
				$SumRequestCount = mysql_result($result3,0,"sum(RequestCount)");
				$SumUploadCount	= mysql_result($result3,0,"sum(UploadCount)");
				$SumDownloadCount = mysql_result($result3,0,"sum(DownloadCount)");				
				
				$sql = "UPDATE fikcdn_buy SET request_total='$SumRequestCount',up_dataflow_total='$SumUploadCount',down_dataflow_total='$SumDownloadCount' WHERE id=$this_buy_id";
		
				$result4 = mysql_query($sql,$db_link);
				if(!$result4)
				{
					echo mysql_error($db_link);
				}
			}
			
			// 套餐超时 或者 流量用完后自动停止域名加速
			if($auto_stop_buy)
			{
				if( ($timenow >= $end_time_buy) || ($SumDownloadCountMB > $data_flow_buy) )
				{
					//查找这个套餐的所有域名，并且停止
					$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$this_buy_id'";
					$result10 = mysql_query($sql,$db_link);
					$row_count10 = mysql_num_rows($result10);
					if($result10 && $row_count10>0)
					{
						for($iii=0;$iii<$row_count10;$iii++)
						{
							$stop_domain_id	 = mysql_result($result10,$iii,"id");
							$stop_group_id = mysql_result($result10,$iii,"group_id");
							$stop_hostname = mysql_result($result10,$iii,"hostname");
							$stop_status = mysql_result($result10,$iii,"status");
							
							if($stop_status==$PubDefine_HostStatusOk)
							{							
								//修改域名状态为停止
								$sql = "UPDATE fikcdn_domain SET status=$PubDefine_HostStatusStop WHERE id=$stop_domain_id";
								$result21 = mysql_query($sql,$db_link);
								
								//删除还没有执行完成的任务
								$sql = "DELETE FROM fikcdn_task WHERE domain_id=$stop_domain_id AND type=$PubDefine_TaskModifyDomainStatus";
								$result22 = mysql_query($sql,$db_link);
								
								$sql = "SELECT * FROM fikcdn_node WHERE groupid='$stop_group_id'";	
								$result11 = mysql_query($sql,$db_link);
								$row_count11 = mysql_num_rows($result11);
								if($result11 && $row_count11>0)
								{
									for($kkk=0;$kkk<$row_count11;$kkk++)
									{
										$stop_node_id	 = mysql_result($result11,$kkk,"id");
										$stop_node_ip 		 = mysql_result($result11,$kkk,"ip");
										$stop_node_password	 = mysql_result($result11,$kkk,"password");
										$stop_node_admin_port = mysql_result($result11,$kkk,"admin_port");
										$stop_node_auth_domain= mysql_result($result11,$kkk,"auth_domain");
										$stop_node_SessionID	 = mysql_result($result11,$kkk,"SessionID");
										
										//加入后台任务
										$timenow2 = time();
										$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
										VALUES(NULL,'$username',$PubDefine_TaskModifyDomainStatus,$timenow2,$stop_domain_id,$stop_node_id,$this_product_id,$this_buy_id,'$stop_hostname',$stop_group_id)";
										$result12 = mysql_query($sql,$db_link);
									}	
								}
							}
						}
					}
				}
			}			
		}	
		
		$sql = "SELECT * FROM fikcdn_domain";
		$result = mysql_query($sql,$db_link);
		if(!$result)
		{	
			exit();
		}
		
		$row_count = mysql_num_rows($result);
		for($i=0;$i<$row_count;$i++)
		{
			$this_domain_id	 = mysql_result($result,$i,"id");
			$this_buy_id = mysql_result($result,$i,"buy_id");
			$this_group_id_id = mysql_result($result,$i,"group_id");

			$SumRequestCount=0;
			$SumUploadCount=0;
			$SumDownloadCount=0;
			$SumIpCount=0;
			
			//统计当月某个套餐发送数据的总数
			$sql = "SELECT sum(RequestCount),sum(UploadCount),sum(DownloadCount),sum(IpCount) FROM domain_stat_group_day WHERE domain_id=$this_domain_id AND time>='$timeval1' AND  time<$timeval2";		
			$result2 = mysql_query($sql,$db_link);
			if($result2 && mysql_num_rows($result2)>0)
			{
				$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
				$SumUploadCount	= mysql_result($result2,0,"sum(UploadCount)");
				$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
				$SumIpCount	= mysql_result($result2,0,"sum(IpCount)");		
			}
			
			if(strlen($SumRequestCount)<=0) $SumRequestCount=0;
			if(strlen($SumUploadCount)<=0) $SumUploadCount=0;
			if(strlen($SumDownloadCount)<=0) $SumDownloadCount=0;
			if(strlen($SumIpCount)<=0) $SumIpCount=0;
			
			//GB
			$SumUploadCount2 = round(($SumUploadCount/(1024)),2);
			$SumDownloadCount2 = round(($SumDownloadCount/(1024)),2);		
			
			//echo "<br />SumUploadCount2=".$SumUploadCount2;
			//echo "<br />SumDownloadCount2=".$SumDownloadCount2;
			
			/*
			$sql = "INSERT INTO domain_stat_product_month(id,buy_id,time,RequestCount,UploadCount,DownloadCount,IpCount) 
				VALUES(NULL,$this_buy_id,$timeval1,$SumRequestCount,$SumUploadCount,$SumDownloadCount,$SumIpCount) ON DUPLICATE KEY UPDATE RequestCount='$SumRequestCount',UploadCount='$SumUploadCount',DownloadCount='$SumDownloadCount',IpCount='$SumIpCount'";
			$result3 = mysql_query($sql,$db_link);	
			*/	

			$sql = "SELECT * FROM domain_stat_month WHERE domain_id=$this_domain_id AND time=$timeval1";
			$result3 = mysql_query($sql,$db_link);
			if(	$result3 && mysql_num_rows($result3)>0)
			{
				$sql = "UPDATE domain_stat_month SET RequestCount='$SumRequestCount',UploadCount='$SumUploadCount2',DownloadCount='$SumDownloadCount2',IpCount='$SumIpCount' 
								WHERE domain_id=$this_domain_id AND time=$timeval1";
		
				$result3 = mysql_query($sql,$db_link);
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}
			else
			{			
				$sql = "INSERT INTO domain_stat_month(id,buy_id,domain_id,time,RequestCount,UploadCount,DownloadCount,IpCount) 
								VALUES(NULL,$this_buy_id,$this_domain_id,$timeval1,'$SumRequestCount','$SumUploadCount2','$SumDownloadCount2','$SumIpCount')";

				$result3 = mysql_query($sql,$db_link);	
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
			}			
			
			//获取累计总流量
			$sql = "SELECT sum(UploadCount),sum(DownloadCount),sum(RequestCount) FROM domain_stat_month WHERE domain_id='$this_domain_id'";
			$result3 = mysql_query($sql,$db_link);				
			if($result3 && mysql_num_rows($result3)>0)
			{
				$SumRequestCount = mysql_result($result3,0,"sum(RequestCount)");
				$SumUploadCount	= mysql_result($result3,0,"sum(UploadCount)");
				$SumDownloadCount = mysql_result($result3,0,"sum(DownloadCount)");				
				
				$sql = "UPDATE fikcdn_domain SET request_total='$SumRequestCount',up_dataflow_total='$SumUploadCount',down_dataflow_total='$SumDownloadCount' WHERE id=$this_domain_id";
				$result4 = mysql_query($sql,$db_link);
				if(!$result4)
				{
					echo mysql_error($db_link);
				}
			}
		}
		
		//1 号 和 15 号自动清理mysql日志
		$sql = 'RESET MASTER';
		$result4 = mysql_query($sql,$db_link);
	}
	else if($sAction=="max_bandwidth")
	{
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{	
			exit();
		}
		
		$timenow = time();
		$stattime = mktime(date("H",$timenow),0,0,date("m",$timenow),date("d",$timenow),date("Y",$timenow));
		
		$timeval = $stattime-(60*60);
		$timeval2 = $stattime;	
		
		//$stattime= $stattime-60*60;
		
		echo "$timeval=".date("Y-m-d H:i:s",$timeval)."<br/>";
		echo "$timeval2=".date("Y-m-d H:i:s",$timeval2)."<br/>";
		
		// 汇总并计算产品套餐的带宽
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
				
				$max_bandwidth_down = 0;
				$max_bandwidth_up=0;
				$sql = "SELECT max(bandwidth_down),max(bandwidth_up) FROM domain_stat_host_bandwidth WHERE time>='$timeval' AND time<'$timeval2' AND domain_id='$this_domain_id'";
				//echo $sql."<br /><br />";
				$result2 = mysql_query($sql,$db_link);	
				if($result2 && mysql_num_rows($result2)>0)
				{
					$max_bandwidth_down	 = mysql_result($result2,0,"max(bandwidth_down)");
					$max_bandwidth_up = mysql_result($result2,0,"max(bandwidth_up)");
				}
				
				
				// MB
				$sum_down_increase=0;
				$sum_up_increase=0;
				$sum_RequestCount_increase=0;
				$sql = "SELECT sum(down_increase),sum(up_increase),sum(RequestCount_increase) FROM domain_stat_host_bandwidth WHERE time>='$timeval' AND time<'$timeval2' AND domain_id='$this_domain_id'";
				echo $sql."<br /><br />";				
				$result2 = mysql_query($sql,$db_link);	
				if($result2 && mysql_num_rows($result2)>0)
				{
					$sum_down_increase	 = mysql_result($result2,0,"sum(down_increase)");
					$sum_up_increase = mysql_result($result2,0,"sum(up_increase)");
					$sum_RequestCount_increase = mysql_result($result2,0,"sum(RequestCount_increase)");
				}
								
				/*
				$max_bandwidth_up=0;
				$sql = "SELECT max(bandwidth_up) FROM domain_stat_host_bandwidth WHERE time>=$timeval AND domain_id=$this_domain_id AND Host='$this_hostname'";
				$result2 = mysql_query($sql,$db_link);			
				if($result2 && mysql_num_rows($result2)>0)
				{
					$max_bandwidth_up = mysql_result($result2,0,"max(bandwidth_up)");
				}
				*/
				
				if(strlen($max_bandwidth_down)<=0) $max_bandwidth_down=0;	
				if(strlen($max_bandwidth_up)<=0) $max_bandwidth_up=0;
				
				if(strlen($sum_down_increase)<=0) $sum_down_increase=0;
				if(strlen($sum_up_increase)<=0) $sum_up_increase=0;
				if(strlen($sum_RequestCount_increase)<=0) $sum_RequestCount_increase=0;
				
				// 汇总套餐带宽总数(一小时峰值）
				if( strlen($aryBuyBandwidth[$this_buy_id]["down"])<=0)
				{
					$aryBuyBandwidth[$this_buy_id]["down"] = $max_bandwidth_down;
				}
				else
				{
					$aryBuyBandwidth[$this_buy_id]["down"] += $max_bandwidth_down;
				}
				
				if( strlen($aryBuyBandwidth[$this_buy_id]["up"])<=0)
				{
					$aryBuyBandwidth[$this_buy_id]["up"] = $max_bandwidth_up;
				}
				else
				{
					$aryBuyBandwidth[$this_buy_id]["up"] += $max_bandwidth_up;
				}
				
				// 汇总套餐流量总数(一小时总数)
				if( strlen($aryBuyBandwidth[$this_buy_id]["down_inc"])<=0)
				{
					$aryBuyBandwidth[$this_buy_id]["down_inc"] = $sum_down_increase;
				}
				else
				{
					$aryBuyBandwidth[$this_buy_id]["down_inc"] += $sum_down_increase;
				}
				
				if( strlen($aryBuyBandwidth[$this_buy_id]["up_inc"])<=0)
				{
					$aryBuyBandwidth[$this_buy_id]["up_inc"] = $sum_up_increase;
				}
				else
				{
					$aryBuyBandwidth[$this_buy_id]["up_inc"] += $sum_up_increase;
				}
												
				if( strlen($aryBuyBandwidth[$this_buy_id]["req_inc"])<=0)
				{
					$aryBuyBandwidth[$this_buy_id]["req_inc"] = $sum_RequestCount_increase;
				}
				else
				{
					$aryBuyBandwidth[$this_buy_id]["req_inc"] += $sum_RequestCount_increase;
				}
				
				/*
				echo "this_hostname=".$this_hostname."<br />";
				echo "stattime=".date("Y-m-d H:i:s",$stattime)."&nbsp;&nbsp;&nbsp;";
				echo "max_bandwidth_down=".$max_bandwidth_down."&nbsp;&nbsp;&nbsp;";
				echo "max_bandwidth_up=".$max_bandwidth_up."&nbsp;&nbsp;&nbsp;";
				
				echo "sum_down_increase=".$sum_down_increase."&nbsp;&nbsp;&nbsp;";
				echo "sum_up_increase=".$sum_up_increase."&nbsp;&nbsp;&nbsp;";
				echo "sum_RequestCount_increase=".$sum_RequestCount_increase."<br />";
				*/
				
				// 删除已有的
				$sql = "DELETE FROM domain_stat_host_max_bandwidth WHERE time='$stattime' AND domain_id='$this_domain_id'";
				$result3 = mysql_query($sql,$db_link);	
				
				$sql = "INSERT INTO domain_stat_host_max_bandwidth(id,group_id,buy_id,domain_id,time,Host,bandwidth_down,bandwidth_up,down_increase,up_increase,RequestCount_increase) 
							VALUES(NULL,$this_group_id,$this_buy_id,$this_domain_id,$stattime,'$this_hostname','$max_bandwidth_down','$max_bandwidth_up','$sum_down_increase','$sum_up_increase','$sum_RequestCount_increase')";

				$result3 = mysql_query($sql,$db_link);	
				if(!$result3)
				{
					echo mysql_error($db_link);
				}
				
				/*
				$sql = "SELECT * FROM domain_stat_host_max_bandwidth WHERE time=$stattime AND buy_id=$this_buy_id AND Host='$this_hostname'";
				$result3 = mysql_query($sql,$db_link);
				if(	$result3 && mysql_num_rows($result3)>0)
				{
					$sql = "UPDATE domain_stat_host_max_bandwidth SET bandwidth_down='$max_bandwidth_down',bandwidth_up='$max_bandwidth_up' 
									WHERE time=$stattime AND buy_id=$this_buy_id AND Host='$this_hostname'";
			
					$result3 = mysql_query($sql,$db_link);
					if(!$result3)
					{
						echo mysql_error($db_link);
					}
				}
				else
				{			
					$sql = "INSERT INTO domain_stat_host_max_bandwidth(id,group_id,buy_id,time,Host,bandwidth_down,bandwidth_up) 
								VALUES(NULL,$this_group_id,$this_buy_id,$stattime,'$this_hostname','$max_bandwidth_down','$max_bandwidth_up')";
	
					$result3 = mysql_query($sql,$db_link);	
					if(!$result3)
					{
						echo mysql_error($db_link);
					}
				}
				*/				
			}
						
			// 汇总并计算产品套餐的带宽
			$sql = "select * from fikcdn_buy";
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count = mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$this_buy_id	 = mysql_result($result,$i,"id");
					
					$max_bandwidth_down = $aryBuyBandwidth[$this_buy_id]["down"];
					$max_bandwidth_up = $aryBuyBandwidth[$this_buy_id]["up"];
					
					$sum_down_increase = $aryBuyBandwidth[$this_buy_id]["down_inc"];
					$sum_up_increase = $aryBuyBandwidth[$this_buy_id]["up_inc"];
					$sum_RequestCount_increase = $aryBuyBandwidth[$this_buy_id]["req_inc"];
					
					if(strlen($max_bandwidth_down)<=0) $max_bandwidth_down=0;
					if(strlen($max_bandwidth_up)<=0) $max_bandwidth_up=0;
					
					if(strlen($sum_down_increase)<=0) $sum_down_increase=0;
					if(strlen($sum_up_increase)<=0) $sum_up_increase=0;
					if(strlen($sum_RequestCount_increase)<=0) $sum_RequestCount_increase=0;
					
					echo "product:  max_bandwidth_down=".$max_bandwidth_down."&nbsp;&nbsp;&nbsp;";
					echo "product:  max_bandwidth_up=".$max_bandwidth_up."&nbsp;&nbsp;&nbsp;";
					
					echo "product:  sum_down_increase=".$sum_down_increase."&nbsp;&nbsp;&nbsp;";
					echo "product:  sum_up_increase=".$sum_up_increase."&nbsp;&nbsp;&nbsp;";
					echo "product:  sum_RequestCount_increase=".$sum_RequestCount_increase."<br />";
					
					$sql = "DELETE FROM domain_stat_product_max_bandwidth WHERE time='$stattime' AND buy_id='$this_buy_id'";
					$result3 = mysql_query($sql,$db_link);	
					
					$sql = "INSERT INTO domain_stat_product_max_bandwidth(id,group_id,buy_id,time,bandwidth_down,bandwidth_up,down_increase,up_increase,RequestCount_increase) 
							VALUES(NULL,0,$this_buy_id,$stattime,'$max_bandwidth_down','$max_bandwidth_up','$sum_down_increase','$sum_up_increase','$sum_RequestCount_increase')";

					$result3 = mysql_query($sql,$db_link);
					if(!$result3)
					{
						echo mysql_error($db_link);
					}
				}
			}
			
			// 统计节点的最大带宽，一小时的流量
			$sql = "select * from fikcdn_node";
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count = mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$this_node_id	 = mysql_result($result,$i,"id");
					$this_node_type	 = mysql_result($result,$i,"type");
					$this_node_group_id	 = mysql_result($result,$i,"groupid");
					
					$max_bandwidth_down = 0;
					$max_bandwidth_up=0;
					$max_upstream_bandwidth_down = 0;
					$max_upstream_bandwidth_up=0;
									
					$sql = "SELECT max(bandwidth_down),max(bandwidth_up),max(upstream_bandwidth_down),max(upstream_bandwidth_up) FROM realtime_list WHERE node_id='$this_node_id' AND time>='$timeval' AND time<'$timeval2' ";
					$result2 = mysql_query($sql,$db_link);	
					if($result2 && mysql_num_rows($result2)>0)
					{
						$max_bandwidth_down	 = mysql_result($result2,0,"max(bandwidth_down)");
						$max_bandwidth_up = mysql_result($result2,0,"max(bandwidth_up)");
						$max_upstream_bandwidth_down = mysql_result($result2,0,"max(upstream_bandwidth_down)");
						$max_upstream_bandwidth_up = mysql_result($result2,0,"max(upstream_bandwidth_up)");
					}					
					
					$sum_down_increase = 0;
					$sum_up_increase=0;
					$sum_upstream_down_increase = 0;
					$sum_upstream_up_increase=0;
					
					// KB
					$sql = "SELECT sum(down_increase),sum(up_increase),sum(upstream_down_increase),sum(upstream_up_increase) FROM realtime_list WHERE node_id='$this_node_id' AND time>='$timeval' AND time<'$timeval2'";
					$result2 = mysql_query($sql,$db_link);	
					if($result2 && mysql_num_rows($result2)>0)
					{
						$sum_down_increase	 = mysql_result($result2,0,"sum(down_increase)");
						$sum_up_increase = mysql_result($result2,0,"sum(up_increase)");
						$sum_upstream_down_increase = mysql_result($result2,0,"sum(upstream_down_increase)");
						$sum_upstream_up_increase = mysql_result($result2,0,"sum(upstream_up_increase)");
					}
					
					// 删除已有的
					$sql = "DELETE FROM realtime_list_max WHERE node_id='$this_node_id' AND time='$stattime'";
					$result3 = mysql_query($sql,$db_link);		
								
					$sql = "INSERT INTO realtime_list_max(id,group_id,node_id,time,bandwidth_down,bandwidth_up,down_increase,up_increase,upstream_bandwidth_down,upstream_bandwidth_up,upstream_down_increase,upstream_up_increase) 
							VALUES(NULL,'$this_node_group_id',$this_node_id,$stattime,'$max_bandwidth_down','$max_bandwidth_up','$sum_down_increase','$sum_up_increase','$max_upstream_bandwidth_down','$max_upstream_bandwidth_up','$sum_upstream_down_increase','$sum_upstream_up_increase')";
					$result3 = mysql_query($sql,$db_link);	
					if(!$result3)
					{
						echo mysql_error($db_link);
					}				
				}
			}
			
			$sum_down_increase =0;
			$sum_up_increase =0;
			$sum_upstream_down_increase =0;
			$sum_upstream_up_increase =0;
												
			//所有节点的流量
			$sql = "SELECT sum(user_down),sum(user_up),sum(upstream_down),sum(upstream_up) FROM realtime_list_all WHERE time>='$timeval' AND time<'$timeval2'";
			$result2 = mysql_query($sql,$db_link);	
			if($result2 && mysql_num_rows($result2)>0)
			{
				$sum_down_increase	 = mysql_result($result2,0,"sum(user_down)");
				$sum_up_increase = mysql_result($result2,0,"sum(user_up)");
				$sum_upstream_down_increase = mysql_result($result2,0,"sum(upstream_down)");
				$sum_upstream_up_increase = mysql_result($result2,0,"sum(upstream_up)");
			}
			
			// MB
			if(strlen($sum_down_increase)<=0) $sum_down_increase=0;
			if(strlen($sum_up_increase)<=0) $sum_up_increase=0;
			if(strlen($sum_upstream_down_increase)<=0) $sum_upstream_down_increase=0;
			if(strlen($sum_upstream_up_increase)<=0) $sum_upstream_up_increase=0;								
			
			// 删除已有的
			$sql = "DELETE FROM realtime_list_all_host WHERE time='$stattime'";
			$result3 = mysql_query($sql,$db_link);			
			
			$sql = "INSERT INTO realtime_list_all_host(id,time,user_down,user_up,upstream_down,upstream_up) 
					VALUES(NULL,'$stattime','$sum_down_increase','$sum_up_increase','$sum_upstream_down_increase','$sum_upstream_up_increase')";
			$result3 = mysql_query($sql,$db_link);	
			if(!$result3)
			{
				echo mysql_error($db_link);
			}			
			
			mysql_close($db_link);
		}				
	}
}

$end_time = time();
echo '<br />execute timeval: '.($end_time-$begin_time);

?>