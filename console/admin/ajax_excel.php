<?php
include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once('../function/define.php');
include_once("function_admin.php");
include_once('../function/Myfunction.php');

//是否登录
if(!FuncAdmin_IsLogin())
{
	$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoLogin);
	PubFunc_EchoJsonAndExit($aryResult,NULL);
}	

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];
	
$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod=="excel")
{
	if($sAction=="node")
	{
		ob_end_clean();
		$sType 		= isset($_GET['type'])?$_GET['type']:'';
		$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$gid 		= isset($_GET['gid'])?$_GET['gid']:'all';
		
		if($fikcdn_admin_power!=10)
		{
				echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
				exit;
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			if(strlen($sKeyword)<=0)
			{
			
			if(is_numeric($gid))
			{
				$sql="select * from fikcdn_node where groupid=$gid";
			}else{
				$sql	= "select * from fikcdn_node";	
			}
		}
			else{
				$sql_and="";
			if(is_numeric($gid))
			{
				$sql_and="AND groupid=$gid ";
			}
			$sKeyword 	= mysql_real_escape_string($sKeyword); 
			if($sType=="ip")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE ip='$sKeyword' $sql_and";
			}
			else if($sType=="unicom_ip")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE unicom_ip='$sKeyword' $sql_and";
			}
			else if($sType=="hardware")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE auth_domain='$sKeyword' $sql_and";
			}
			else if($sType=="nodename")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE name like '%$sKeyword%' $sql_and";
			}
			else
			{
				mysql_close($db_link);
				exit();
			}		
			}
			
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				if($row_count>0)
				{
					// excel名称
					$strName       = '服务器列表';
					// excel 标头（写死）
					$arrExcelTitle = array('序号','服务器IP一','服务器IP二','服务器名称','Fikker 版本','状态','备注');
					
					// 组织新数组内容（要和标头对应上）
					$arrExcelCont = array();
					for($i=0;$i<$row_count;$i++)
					{
						$arrExcelCont[$i]['id']  			= mysql_result($result,$i,"id");
						$arrExcelCont[$i]['ip']  		 	= mysql_result($result,$i,"ip");
						$arrExcelCont[$i]['unicom_ip']	 	= mysql_result($result,$i,"unicom_ip");
						$arrExcelCont[$i]['name']   		= mysql_result($result,$i,"name");
						$fik_version   	= mysql_result($result,$i,"fik_version");	
						$version_ext	= mysql_result($result,$i,"version_ext");
						$is_close		= mysql_result($result,$i,"is_close");						
						$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
						$arrExcelCont[$i]['fikcdn_version']	= $version_ext.'/'.$show_version;
						$arrExcelCont[$i]['status']			= $is_close ?'停止中':'启用中';
						$arrExcelCont[$i]['note']   		= mysql_result($result,$i,"note");
							
					}

					//导出Excel
					ExcelExport($strName,$arrExcelTitle,$arrExcelCont);
					
				}else{
						echo "<script>alert('无内容导出！');</script>";
				}
		
		}
			mysql_close($db_link);
		}		
	}else if($sAction=="host"){
		ob_end_clean();
		$buy_id 	= isset($_GET['buy_id'])?$_GET['buy_id']:'all';
		$sType 		= isset($_GET['type'])?$_GET['type']:'';
		$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{		
			if(strlen($sKeyword)<=0)
			{
			
			if(is_numeric($gid))
			{
				$sql="select * from fikcdn_domain where buy_id='$buy_id'";
			}else{
				$sql = "SELECT * FROM fikcdn_domain;";	
			}
			}
			else{
				$sql_and="";
			if(is_numeric($gid))
			{
				$sql_and="AND buy_id='$buy_id' ";
			}
			$sKeyword 	= mysql_real_escape_string($sKeyword); 
			if($sType=="domain")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE hostname like '%$sKeyword%' $sql_and";
			}
			else if($sType=="srcip")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE upstream like '%$sKeyword%' $sql_and";
			}
			else if($sType=="srcip2")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE unicom_ip like '%$sKeyword%' $sql_and";
			}
			else if($sType=="owner")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE username like '%$sKeyword%' $sql_and";
			}
			
			}
			
			$result = mysql_query($sql,$db_link);
			if($result)
			{
			
			$row_count=mysql_num_rows($result);

			if($row_count>0){
			
			$timeval1 = mktime(0,0,0,date("m"),0,date("Y"));
			$timeval2 = mktime(0,0,0,(date("m")+1),0,date("Y"));
			
			// excel名称
			$strName       = '域名列表';
			// excel 标头（写死）
			$arrExcelTitle = array('序号','网站域名','源站IP','所属用户','本月累计流量','状态','所属套餐','备注');
			
			// 组织新数组内容（要和标头对应上）
				$arrExcelCont = array();			
			for($i=0;$i<$row_count;$i++)
			{
				$arrExcelCont[$i]['id']	= mysql_result($result,$i,"id");
				$hostname	= mysql_result($result,$i,"hostname");					$arrExcelCont[$i]['hostname']	= $hostname;	
				$upstream		= mysql_result($result,$i,"upstream");
				$unicom_ip		= mysql_result($result,$i,"unicom_ip");
				
				if(strlen($upstream)>0 && strlen($unicom_ip)>0)
				{
					$upstream = $upstream.'；'.$unicom_ip;
				}
				else if(strlen($upstream)<=0)
				{
					$upstream = $unicom_ip;
				}	
				$arrExcelCont[$i]['upstream']	= $upstream;
				$arrExcelCont[$i]['username']	= mysql_result($result,$i,"username");			
				$this_buy_id	= mysql_result($result,$i,"buy_id");
				$status   		= mysql_result($result,$i,"status");	
				$sql = "SELECT sum(DownloadCount),sum(RequestCount) FROM domain_stat_group_day WHERE buy_id='$this_buy_id' AND Host='$hostname' AND time>=$timeval1 AND time<$timeval2";
				$result2 = mysql_query($sql,$db_link);	
				if($result2 && mysql_num_rows($result2)>0)				
				{
					$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
					//$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
				}
				$arrExcelCont[$i]['SumDownloadCount']	= PubFunc_MBToString($SumDownloadCount);
				$arrExcelCont[$i]['status']	= $PubDefine_HostStatus[$status];
				$sql = "SELECT * FROM fikcdn_buy WHERE id='$this_buy_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{		
					$product_id		 = mysql_result($result2,0,"product_id");
				
					$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$product_name  		= mysql_result($result2,0,"name");
						//$product_name = $product_name.'('.$buy_id.')';
					}
				}
				$arrExcelCont[$i]['product_name']	= $product_name;
				
				
				$arrExcelCont[$i]['note']		= mysql_result($result,$i,"note");
	
			}
		//echo serialize($arrExcelCont);exit;
			//导出Excel
			ExcelExport($strName,$arrExcelTitle,$arrExcelCont);
					
			}else{
						echo "<script>alert('无内容导出！');</script>";
						exit;
						mysql_close($db_link);
				}
			
		}
		
		mysql_close($db_link);
				
		}
	}
	else if($sAction=="domain_max")
	{
		ob_end_clean();
		$date 		= isset($_GET['date'])?$_GET['date']:'100';
		$domain_id 	= isset($_GET['domain_id'])?$_GET['domain_id']:'';
		$show_name 	= isset($_GET['show_name'])?$_GET['show_name']:'';
		
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}		
		
		if(!is_numeric($domain_id))
		{
			echo "<script>alert('对不起，参数错误');window.history.go(-1);</script>";
			exit;
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			if($date==100)
			{
				$timeval1 = mktime(0,0,0,date("m"),1,date("Y"));
				$timeval2 = mktime(0,0,0,date("m")+1,1,date("Y"));
			}
			else if($date==101)
			{
				$timeval1 = mktime(0,0,0,date("m")-1,1,date("Y"));
				$timeval2 = mktime(0,0,0,date("m"),1,date("Y"));
			}
			else
			{
				$timeval1 = mktime(0,0,0,$date,1,date("Y"));
				$timeval2 = mktime(0,0,0,$date+1,1,date("Y"));
			}
							
			$arg_bandwidth_down=0;
			$arg_bandwidth_up=0;
			$arg_RequestCount=0;
			$arg_DownloadCount=0;
			$arg_UploadCount=0;
			$arg_value_sum=0;
			
			// excel名称
			$strName       = '域名每日最大带宽数据('.$show_name.')';
			// excel 标头（写死）
			$arrExcelTitle = array('峰值带宽发生时间','当日下载峰值带宽','当时上传带宽','用户日下载流量','用户日上传流量','日请求量');
			$arrExcelCont = array();
			
			$sql = "SELECT * FROM domain_stat_group_day where domain_id='$domain_id' AND time>='$timeval1' AND time<'$timeval2' ";
			//echo $sql;
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$id  			= mysql_result($result,$i,"id");	
					$group_id  		= mysql_result($result,$i,"group_id");	
					$this_buy_id	= mysql_result($result,$i,"buy_id");	
					$this_time  	= mysql_result($result,$i,"time");	
					$RequestCount   = mysql_result($result,$i,"RequestCount");	
					$UploadCount	= mysql_result($result,$i,"UploadCount");
					$DownloadCount	= mysql_result($result,$i,"DownloadCount");
					$time_for_max	= mysql_result($result,$i,"time_for_max");	
					$bandwidth_down	= mysql_result($result,$i,"bandwidth_down");	
					$bandwidth_up	= mysql_result($result,$i,"bandwidth_up");		
					
					if($time_for_max<=0)
					{
					//	$time_for_max = $this_time;
					}
					if($time_for_max>0)
					{						
						$arrExcelCont[$i]['time']	= date("Y-m-d H:i:s",$time_for_max);
						$arrExcelCont[$i]['bandwidth_down']	= $bandwidth_down.' Mbps';
						$arrExcelCont[$i]['bandwidth_up']	= $bandwidth_up.' Mbps';
						$arrExcelCont[$i]['DownloadCount']	= PubFunc_MBToString($DownloadCount);
						$arrExcelCont[$i]['UploadCount']	= PubFunc_MBToString($UploadCount);
						$arrExcelCont[$i]['RequestCount']	= $RequestCount;
						
						$arg_bandwidth_down+=$bandwidth_down;
						$arg_bandwidth_up+=$bandwidth_up;
						$arg_RequestCount+=$RequestCount;
						$arg_DownloadCount+=$DownloadCount;
						$arg_UploadCount+=$UploadCount;
						$arg_value_sum++;
					}				
				}		
			}
			
			if($arg_value_sum>0)
			{
				$arg_bandwidth_down = round($arg_bandwidth_down/$arg_value_sum,3);
				$arg_bandwidth_up = round($arg_bandwidth_up/$arg_value_sum,3);
				$arg_RequestCount = round($arg_RequestCount/$arg_value_sum,0);
				$arg_DownloadCount = round($arg_DownloadCount/$arg_value_sum,2);
				$arg_UploadCount = round($arg_UploadCount/$arg_value_sum,2);
				
				$i++;
				$arrExcelCont[$i]['time']	= '平均值';
				$arrExcelCont[$i]['bandwidth_down']	= $arg_bandwidth_down.' Mbps';
				$arrExcelCont[$i]['bandwidth_up']	= $arg_bandwidth_up.' Mbps';
				$arrExcelCont[$i]['DownloadCount']	= PubFunc_MBToString($arg_DownloadCount);
				$arrExcelCont[$i]['UploadCount']	= PubFunc_MBToString($arg_UploadCount);
				$arrExcelCont[$i]['RequestCount']	= $arg_RequestCount;
			}
			
			//var_dump($arrExcelCont);
			
			ExcelExport($strName,$arrExcelTitle,$arrExcelCont);		
			
			mysql_close($db_link);	
		}	
	}
	else if($sAction=="buy_max")
	{
		ob_end_clean();
		$date 		= isset($_GET['date'])?$_GET['date']:'100';
		$buy_id 	= isset($_GET['buy_id'])?$_GET['buy_id']:'';
		$show_name 	= isset($_GET['show_name'])?$_GET['show_name']:'';
		
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}		
		
		if(!is_numeric($buy_id))
		{
			echo "<script>alert('对不起，参数错误');window.history.go(-1);</script>";
			exit;
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			if($date==100)
			{
				$timeval1 = mktime(0,0,0,date("m"),1,date("Y"));
				$timeval2 = mktime(0,0,0,date("m")+1,1,date("Y"));
			}
			else if($date==101)
			{
				$timeval1 = mktime(0,0,0,date("m")-1,1,date("Y"));
				$timeval2 = mktime(0,0,0,date("m"),1,date("Y"));
			}
			else
			{
				$timeval1 = mktime(0,0,0,$date,1,date("Y"));
				$timeval2 = mktime(0,0,0,$date+1,1,date("Y"));
			}
							
			$arg_bandwidth_down=0;
			$arg_bandwidth_up=0;
			$arg_RequestCount=0;
			$arg_DownloadCount=0;
			$arg_UploadCount=0;
			$arg_value_sum=0;
			
			// excel名称
			$strName       = '套餐每日最大带宽数据('.$show_name.')';
			// excel 标头（写死）
			$arrExcelTitle = array('峰值带宽发生时间','当日下载峰值带宽','当时上传带宽','用户日下载流量','用户日上传流量','日请求量');
			$arrExcelCont = array();
			
			$sql = "SELECT * FROM domain_stat_product_day where buy_id='$buy_id' AND time>='$timeval1' AND time<'$timeval2' ";
			//echo $sql;
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				for($i=0;$i<$row_count;$i++)
				{
					$id  			= mysql_result($result,$i,"id");	
					$this_buy_id	= mysql_result($result,$i,"buy_id");	
					$this_time  	= mysql_result($result,$i,"time");	
					$RequestCount   = mysql_result($result,$i,"RequestCount");	
					$UploadCount	= mysql_result($result,$i,"UploadCount");
					$DownloadCount	= mysql_result($result,$i,"DownloadCount");
					$time_for_max	= mysql_result($result,$i,"time_for_max");	
					$bandwidth_down	= mysql_result($result,$i,"bandwidth_down");	
					$bandwidth_up	= mysql_result($result,$i,"bandwidth_up");		
					
					if($time_for_max<=0)
					{
					//	$time_for_max = $this_time;
					}
					if($time_for_max>0)
					{						
						$arrExcelCont[$i]['time']	= date("Y-m-d H:i:s",$time_for_max);
						$arrExcelCont[$i]['bandwidth_down']	= $bandwidth_down.' Mbps';
						$arrExcelCont[$i]['bandwidth_up']	= $bandwidth_up.' Mbps';
						$arrExcelCont[$i]['DownloadCount']	= PubFunc_MBToString($DownloadCount);
						$arrExcelCont[$i]['UploadCount']	= PubFunc_MBToString($UploadCount);
						$arrExcelCont[$i]['RequestCount']	= $RequestCount;
						
						$arg_bandwidth_down+=$bandwidth_down;
						$arg_bandwidth_up+=$bandwidth_up;
						$arg_RequestCount+=$RequestCount;
						$arg_DownloadCount+=$DownloadCount;
						$arg_UploadCount+=$UploadCount;
						$arg_value_sum++;
					}				
				}		
			}
			
			if($arg_value_sum>0)
			{
				$arg_bandwidth_down = round($arg_bandwidth_down/$arg_value_sum,3);
				$arg_bandwidth_up = round($arg_bandwidth_up/$arg_value_sum,3);
				$arg_RequestCount = round($arg_RequestCount/$arg_value_sum,0);
				$arg_DownloadCount = round($arg_DownloadCount/$arg_value_sum,2);
				$arg_UploadCount = round($arg_UploadCount/$arg_value_sum,2);
				
				$i++;
				$arrExcelCont[$i]['time']	= '平均值';
				$arrExcelCont[$i]['bandwidth_down']	= $arg_bandwidth_down.' Mbps';
				$arrExcelCont[$i]['bandwidth_up']	= $arg_bandwidth_up.' Mbps';
				$arrExcelCont[$i]['DownloadCount']	= PubFunc_MBToString($arg_DownloadCount);
				$arrExcelCont[$i]['UploadCount']	= PubFunc_MBToString($arg_UploadCount);
				$arrExcelCont[$i]['RequestCount']	= $arg_RequestCount;
			}
			
			//var_dump($arrExcelCont);
			
			ExcelExport($strName,$arrExcelTitle,$arrExcelCont);		
			
			mysql_close($db_link);	
		}	
	}	
}else if($sMod=="task"){
	if($sAction=="del"){
		$task_ids		= isset($_POST['select'])?$_POST['select']:'';
		
		$task_id	= empty($task_ids) ? "":join(',',$task_ids);
		if($task_id==""){
			echo "<script>alert('参数错误');window.history.go(-1);</script>";
			exit;
		}	
			
		//无权限
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "DELETE FROM fikcdn_task WHERE id in ($task_id)";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				echo "<script>alert('删除后台任务失败，操作数据库错误。');window.history.go(-1);</script>";
				exit;
				mysql_close($db_link);
			}
				echo "<script>window.location.href='task_list.php';</script>";
				mysql_close($db_link);
		}	
		else
		{
			echo "<script>alert('删除后台任务失败，连接数据库错误。');window.history.go(-1);</script>";
			mysql_close($db_link);
			exit;
		}	
				
	}else if($sAction=="react"){
			$task_ids		= isset($_POST['select'])?$_POST['select']:'';
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$true	= false;
			foreach($task_ids as $task_id){
				if(!is_numeric($task_id))
				{
						continue;
				}	
				$sql = "UPDATE fikcdn_task SET execute_count=0,result_str='' WHERE id=$task_id";
				$result = mysql_query($sql,$db_link);	
				if(!$result)
				{
					continue;
				}
				$true	= true;
			}
			if($true){
				echo "<script>window.location.href='task_list.php';</script>";
			}else{
				echo "<script>alert('重新将后台任务加入执行队列失败。');window.history.go(-1);</script>";
			}
			mysql_close($db_link);
		}	
		else
		{
			echo "<script>alert('重新将后台任务失败，连接数据库错误。');window.history.go(-1);</script>";
			mysql_close($db_link);
			exit;
		}	
	}else if($sAction=="delall"){
			
		//无权限
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "DELETE FROM fikcdn_task";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				echo "<script>alert('删除后台任务失败，操作数据库错误。');window.history.go(-1);</script>";
				exit;
				mysql_close($db_link);
			}
				echo "<script>window.location.href='task_list.php';</script>";
				mysql_close($db_link);
		}	
		else
		{
			echo "<script>alert('删除后台任务失败，连接数据库错误。');window.history.go(-1);</script>";
			mysql_close($db_link);
			exit;
		}	
				
	}else if($sAction=="delfalse"){
			
		//无权限
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "DELETE FROM fikcdn_task where execute_count>='$PubDefine_TaskMaxExecuteCount' and result_str!=''";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				echo "<script>alert('删除后台任务失败，操作数据库错误。');window.history.go(-1);</script>";
				exit;
				mysql_close($db_link);
			}
				echo "<script>window.location.href='task_list.php';</script>";
				mysql_close($db_link);
		}	
		else
		{
			echo "<script>alert('删除后台任务失败，连接数据库错误。');window.history.go(-1);</script>";
			mysql_close($db_link);
			exit;
		}	
				
	}
}else if($sMod=="domain"){
	if($sAction=="verify"){
		if($fikcdn_admin_power!=10)
		{
			echo "<script>alert('对不起，您无此操作权限');window.history.go(-1);</script>";
			exit;
		}	
			
		$domain_ids		= isset($_POST['select'])?$_POST['select']:'';
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$true	= false;
			foreach($domain_ids as $domain_id){
				if(!is_numeric($domain_id))
				{
						continue;
				}	
				$sql="SELECT * FROM fikcdn_domain WHERE id='$domain_id'";
				$result = mysql_query($sql,$db_link);
				if(!$result || mysql_num_rows($result)<=0)
				{
					continue;
				}
			
			$hostname 		= mysql_result($result,0,"hostname");
			$username 		= mysql_result($result,0,"username");
			$buy_id			= mysql_result($result,0,"buy_id");
			$upstream		= mysql_result($result,0,"upstream");
			$status			= mysql_result($result,0,"status");
		
			//审核已经通过
			if($status!=$PubDefine_HostStatusVerify)
			{
					continue;
			}
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				continue;
			}	
			$product_id	= mysql_result($result,0,"product_id");
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				continue;
			}	
			
			$product_name	= mysql_result($result,0,"name");
			$group_id 		= mysql_result($result,0,"group_id");
			
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				continue;	
			}
			
			$node_count = mysql_num_rows($result);
			for($i=0;$i<$node_count;$i++)
			{
				$node_id 		 = mysql_result($result,$i,"id");
				$node_ip 		 = mysql_result($result,$i,"ip");
				$node_password	 = mysql_result($result,$i,"password");
				$node_admin_port = mysql_result($result,$i,"admin_port");
				$node_auth_domain= mysql_result($result,$i,"auth_domain");
				$node_SessionID	 = mysql_result($result,$i,"SessionID");
				
				//加入后台任务
				$timenow = time();
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
								VALUES(NULL,'$username',$PubDefine_TaskAddProxy,$timenow,$domain_id,$node_id,$product_id,$buy_id,'$hostname',$group_id)";
				$result2 = mysql_query($sql,$db_link);
			}			 	
			
			//修改域名状态
			$sql = "UPDATE fikcdn_domain SET status=$PubDefine_HostStatusOk WHERE id=$domain_id";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				continue;
			}
				$true	= true;
			}
			if($true){
				echo "<script>window.location.href='domain_verify.php';</script>";
			}else{
				echo "<script>alert('审核域名失败。');window.history.go(-1);</script>";
			}
			mysql_close($db_link);
		}	
		else
		{
			echo "<script>alert('审核域名审核失败，连接数据库错误。');window.history.go(-1);</script>";
			mysql_close($db_link);
			exit;
		}	
	}
}



?>
