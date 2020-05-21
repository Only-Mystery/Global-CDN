<?php
include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once('../function/define.php');
include_once("function_admin.php");

//是否登录
if(!FuncAdmin_IsLogin())
{
	$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoLogin);
	PubFunc_EchoJsonAndExit($aryResult,NULL);
}	
	
$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod=="search")
{
	if($sAction=="user")
	{
		$sType 		= isset($_GET['type'])?$_GET['type']:'';
		$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		
		if(strlen($sKeyword)<=0)
		{	
			exit();
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sKeyWord 	= mysql_real_escape_string($sKeyword); 
			if($sType=="realname")
			{
				$sql = "SELECT * FROM fikcdn_client WHERE realname='$sKeyword' LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="username")
			{
				$sql = "SELECT * FROM fikcdn_client WHERE username='$sKeyword' LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="compname")
			{
				$sql = "SELECT * FROM fikcdn_client WHERE company_name like '%$sKeyword%' LIMIT $PubDefine_PageShowNum";
			}
			else
			{
				mysql_close($db_link);
				exit();
			}
			
			echo '<table width="800" border="0" class="dataintable">
					<tr>
						<th align="center" width="150">用户帐号</th>
						<th align="center" width="80">姓名</th> 
						<th align="center" width="205" align="center">公司名称</th>
						<th align="center" width="90">联系电话</th>
						<th align="center" width="90">QQ号</th>	
						<th align="center" width="100">账户余额(元)</th>
						<th align="center" width="100">域名数量</th>						
						<th align="center" width="45" align="center">状态</th>
						<th align="center">操作</th>
					</tr>';
			
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				if($row_count>0)
				{
					for($i=0;$i<$row_count;$i++)
					{
						$id  			= mysql_result($result,$i,"id");	
						$username   	= mysql_result($result,$i,"username");
						$realname   	= mysql_result($result,$i,"realname");		
						$enable_login  	= mysql_result($result,$i,"enable_login");	
						$money  		= mysql_result($result,$i,"money");				
						$register_time	= mysql_result($result,$i,"register_time");	
						$register_ip   	= mysql_result($result,$i,"register_ip");	
						$addr   		= mysql_result($result,$i,"addr");	
						$phone   		= mysql_result($result,$i,"phone");
						$company_name	= mysql_result($result,$i,"company_name");
						$qq	   			= mysql_result($result,$i,"qq");			
						$last_login_time = mysql_result($result,$i,"last_login_time");	
						$last_login_ip	 = mysql_result($result,$i,"last_login_ip");	
						
						$domain_count=0;
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE username='$username'";
						$result2 = mysql_query($sql,$db_link);
						if($result2&&mysql_num_rows($result)>0)
						{
							$domain_count = mysql_result($result2,0,"count(*)");
						}	
				
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
						echo '<td>'.$username.'</td>';
						echo '<td>'.$realname.'</td>';
						echo '<td>'.$company_name.'</td>';
						echo '<td>'.$phone.'</td>';
						echo '<td>'.$qq.'</td>';
						echo '<td>'.$money.'</td>';
						echo '<td>'.$domain_count.'</td>';
						echo '<td>'.$PubDefine_ClientStatus[$enable_login].'</td>';
						echo '<td><a href="fikcdn_modifyuser.php?id='.$id.'" title="修改用户信息">修改信息</a>&nbsp;
								<a href="fikcdn_recharge.php?id='.$id.'" title="账户充值">充值</a>&nbsp;
								<a href="javascript:void(0);" onclick="javescript:FikCdn_DelUser('.$id.');" title="删除用户帐号信息">删除</a>					
								</td>';
						echo '</tr>';
					}
				}
			}
			echo '</table>';
			mysql_close($db_link);
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}
	else if($sAction=="node")
	{
		$sType 		= isset($_GET['type'])?$_GET['type']:'';
		$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$gid 		= isset($_GET['gid'])?$_GET['gid']:'all';
		
		if(strlen($sKeyword)<=0)
		{
			exit();
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql_and="";
			if(is_numeric($gid))
			{
				$sql_and="AND groupid=$gid ";
			}
			
			$sKeyword 	= mysql_real_escape_string($sKeyword); 
			if($sType=="ip")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE ip='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="unicom_ip")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE unicom_ip='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="hardware")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE auth_domain='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="nodename")
			{
				$sql = "SELECT * FROM fikcdn_node WHERE name like '%$sKeyword%' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else
			{
				mysql_close($db_link);
				exit();
			}		
			
			echo '<table width="800" border="0" class="dataintable">
					<tr>
						<th align="center" width="50">序号</th> 
						<th align="center" width="140">服务器IP一</th>
						<th align="center" width="140">服务器IP二</th>				
						<th align="center" width="150">服务器名称</th> 
						<th align="center" width="140" align="center">Fikker 版本</th>
						<th align="center" width="55" align="center">状态</th>
						<th align="center" width="200" align="center">备注</th>
						<th align="center">操作</th>
					</tr>';
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				if($row_count>0)
				{
					for($i=0;$i<$row_count;$i++)
					{
						$id  			= mysql_result($result,$i,"id");
						$name   		= mysql_result($result,$i,"name");
						$ip  		 	= mysql_result($result,$i,"ip");
						$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");
						$port   		= mysql_result($result,$i,"port");
						$admin_port   	= mysql_result($result,$i,"admin_port");
						$add_time   	= mysql_result($result,$i,"add_time");
						$fik_version   	= mysql_result($result,$i,"fik_version");	
						$auth_domain   	= mysql_result($result,$i,"auth_domain");
						$groupid	   	= mysql_result($result,$i,"groupid");
						$status	   		= mysql_result($result,$i,"status");
						$note	   		= mysql_result($result,$i,"note");
						$version_ext	= mysql_result($result,$i,"version_ext");
						$is_close		= mysql_result($result,$i,"is_close");						
						
						$admin_url = $ip.":"."$admin_port"."/fikker/";	
						
						$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
						
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
						echo '<td>'.$id.'</td>';
						echo '<td><a href="login_fikker.php?mod=logging&action=redirect&id='.$id.'" title="进入 Fikker 管理后台" target="_blank" >'.$ip.'</a></td>';
						echo '<td><a href="login_fikker.php?mod=logging&action=redirect&id='.$id.'" title="进入 Fikker 管理后台" target="_blank" >'.$unicom_ip.'</a></td>';								
						echo '<td>'.$name.'</td>';
						//echo '<td>'.$auth_domain.'</td>';
						//echo '<td>'.date("Y-m-d H:i:s",$add_time).'</td>';
						echo '<td>'.$version_ext.'/'.$show_version.'</td>';
						if($is_close)
						{
							echo '<td><span id="span_is_close_'.$id.'">已停止</span></td>';
						}
						else
						{
							echo '<td><span id="span_is_close_'.$id.'">启用中</span></td>';
						}
						echo '<td>'.$note.'</td>';
						echo '<td><a href="fikcdn_statnode.php?id='.$id.'" title="查看服务器流量统计数据">流量统计</a>&nbsp;';
						echo '<a href="fikcdn_modifynode.php?id='.$id.'" title="修改服务器信息">修改</a>&nbsp;';
						echo '<a href="#" onclick="javescript:FikCdn_SameNodeConfig('.$id.','.$groupid.');" title="同步服务器主机管理配置">同步</a>&nbsp;&nbsp;<span id="span_is_close_href_'.$id.'">';
						if($is_close)
						{
							echo '<a href="#" onclick="javescript:FikCdn_StartNode('.$id.','.$groupid.');" title="启用节点">启用</a>';
						}
						else
						{
							echo '<a href="#" onclick="javescript:FikCdn_StopNode('.$id.','.$groupid.');" title="停止节点">停止</a>';
						}
						
						echo '</span>&nbsp;&nbsp;<a href="#" onclick="javescript:FikCdn_NodeDelete('.$id.','.$groupid.');" title="删除服务器">删除</a></td>';
						echo '</tr>';
					}	
				}
			}	
			echo '</table>';	
			mysql_close($db_link);
		}		
	}
	else if($sAction=="domain")
	{
		$sType 		= isset($_GET['type'])?$_GET['type']:'';
		$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$buy_id		= isset($_GET['buy_id'])?$_GET['buy_id']:'all';
		
		if(strlen($sKeyword)<=0)
		{
			exit();
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sKeyword 	= mysql_real_escape_string($sKeyword); 
			
			$sql_and="";
			if(is_numeric($gid))
			{
				$sql_and="AND buy_id=$buy_id ";
			}
				
			if($sType=="domain")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="srcip")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE upstream='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="owner")
			{
				$sql = "SELECT * FROM fikcdn_domain WHERE username='$sKeyword' $sql_and LIMIT $PubDefine_PageShowNum";
			}
			else if($sType=="status")
			{
				$stauts=-1;
				for($i=0;$i<4;$i++)
				{
					if($sKeyword==$PubDefine_HostStatus[$i])
					{
						$stauts = $i;
						break;
					}
				}
				
				if($stauts<0)
				{
					mysql_close($db_link);
					exit();
				}

				$sql = "SELECT * FROM fikcdn_domain WHERE status=$stauts $sql_and LIMIT $PubDefine_PageShowNum";
			}					
			else
			{
				mysql_close($db_link);
				exit();
			}		
				
			//echo $sql;
			echo ' <table width="800" border="0" class="dataintable" id="domain_table">
					<tr id="tr_domain_title">
						<th align="center" width="50">序号</th> 
						<th align="center" width="130">网站域名</th> 
						<th align="center" width="100">源站IP一</th>
						<th align="center" width="100">源站IP二</th>
						<th align="center" width="120">所属用户</th>
						<th align="center" width="50" align="center">状态</th>
						<th align="center" width="80" align="center">所属套餐</th>
						<th align="center" width="80" align="center">月累计流量</th>		
						<th align="center" width="80" align="center">月总请求数</th>
						<th align="center" width="140" align="center">备注</th>								
						<th align="center">操作</th>
					</tr>';
			
			$timeval1 = mktime(0,0,0,date("m"),0,date("Y"));
			$timeval2 = mktime(0,0,0,(date("m")+1),0,date("Y"));
			
			$result = mysql_query($sql,$db_link);
			if($result)
			{
				$row_count=mysql_num_rows($result);
				if($row_count>0)
				{
					for($i=0;$i<$row_count;$i++)
					{						
						$id  			= mysql_result($result,$i,"id");	
						$hostname  		= mysql_result($result,$i,"hostname");	
						$username	 	= mysql_result($result,$i,"username");	
						$add_time  		= mysql_result($result,$i,"add_time");	
						$status   		= mysql_result($result,$i,"status");	
						$this_buy_id	= mysql_result($result,$i,"buy_id");
						$group_id	   	= mysql_result($result,$i,"group_id");		
						$upstream		= mysql_result($result,$i,"upstream");
						$unicom_ip		= mysql_result($result,$i,"unicom_ip");
						$begin_time		= mysql_result($result,$i,"begin_time");	
						$end_time		= mysql_result($result,$i,"end_time");	
						$note			= mysql_result($result,$i,"note");		
						
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
						
						$sql = "SELECT * FROM fikcdn_client WHERE username='$username'";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{		
							$realname		 = mysql_result($result2,0,"realname");
						}
						
						$sql = "SELECT sum(DownloadCount),sum(RequestCount) FROM domain_stat_group_day WHERE buy_id='$this_buy_id' AND Host='$hostname' AND time>=$timeval1 AND time<$timeval2";
						$result2 = mysql_query($sql,$db_link);	
						if($result2 && mysql_num_rows($result2)>0)				
						{
							$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
							//$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
						}
						
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" '.'id="tr_domain_'.$id.'">';
						echo '<td>'.$id.'</td>';
						echo '<td>'.$hostname.'</td>';
						echo '<td>'.$upstream.'</td>';
						echo '<td>'.$unicom_ip.'</td>';
						echo '<td>'.$username.'</td>';
						//echo '<td>'.date("Y-m-d",$add_time).'</td>';
						echo '<td>'.PubFunc_MBToString($SumDownloadCount).'</td>';
						echo '<td>'.$PubDefine_HostStatus[$status]. '</td>';
						echo '<td>'.$product_name.'</td>';
						//echo '<td>'.$SumRequestCount.'</td>';
						echo '<td>'.$note.'</td>';
						echo '<td>  <a href="stat_domain_max_bandwidth.php?domain_id='.$id.'" title="查看此域名流量统计信息">流量统计</a>&nbsp;';
						echo '<a href="fikcdn_modifyhost.php?id='.$id.'" title="修改域名信息">修改</a>&nbsp;';
						if($status==$PubDefine_HostStatusStop )
						{
							echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_DomainStart('.$id.');" title="开始域名加速">开始加速</a>';
						}
						else if($status==$PubDefine_HostStatusOk )
						{
							echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_DomainStop('.$id.');" title="暂停域名加速">暂停加速</a>';
						}
						else if($status==$PubDefine_HostStatusVerify)
						{
							echo '<a href="javascript:void(0);" onclick="javescript:FikCnd_DomainVerify('.$id.');" title="开始加速域名到节点组">通过审核</a>';
						}
						echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DomainDel('.$id.');" title="删除节点信息">删除</a> </td>';
						echo '</tr>';
					}
				}
			}	
		
			echo '</table>';	
			mysql_close($db_link);
		}
	}	
	else if($sAction=="recharge")
	{
		$type 		= isset($_GET['type'])?$_GET['type']:'';
		$keyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
		
		if(strlen($keyword)<=0 || strlen($keyword)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		echo '<table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="65">序号</th>
				<th align="center" width="120">用户名</th>
				<th align="center" width="150">银行流水号</th>
				<th align="center" width="150">银行名称</th>	
				<th align="center" width="70">经办人</th>
				<th align="center" width="140">充值日期</th> 	
				<th align="center" width="80">金额(元)</th>
				<th align="center" width="80">账户余额(元)</th>						
				<th align="center">备注</th>
			</tr>';		
							
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$keyword = mysql_real_escape_string($keyword); 
			$timeval = mysql_real_escape_string($timeval); 	
			
			if($timeval>1000) $timeval=1000;
			
			if($type=="serial_no")
			{	
				if($timeval>0)
				{
					$timeval = (time()-$timeval*60*60*24);
					$sql = "SELECT * FROM fikcdn_recharge WHERE serial_no='$keyword' AND time>'$timeval' ORDER BY id DESC;"; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_recharge WHERE serial_no='$keyword' ORDER BY id DESC;"; 
				}
			}
			else if($type=="username")
			{
				if($timeval>0)
				{
					$timeval = (time()-$timeval*60*60*24);
					$sql = "SELECT * FROM fikcdn_recharge WHERE username='$keyword' AND time>'$timeval' ORDER BY id DESC;"; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_recharge WHERE username='$keyword' ORDER BY id DESC;"; 
				}
			}
			else
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$result = mysql_query($sql,$db_link);
			if($result)
			{	
				$row_count=mysql_num_rows($result);	
				for($i=0;$i<$row_count;$i++)
				{
					$id  			= mysql_result($result,$i,"id");	
					$username   	= mysql_result($result,$i,"username");
					$money   		= mysql_result($result,$i,"money");	
					$time  		 	= mysql_result($result,$i,"time");	
					$transactor   	= mysql_result($result,$i,"transactor");	
					$bank_name   	= mysql_result($result,$i,"bank_name");	
					$serial_no   	= mysql_result($result,$i,"serial_no");
					$balance	   	= mysql_result($result,$i,"balance");
					$note   	    = mysql_result($result,$i,"note");
					
					echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
					echo '<td>'.$id.'</td>';
					echo '<td>'.$username.'</td>';
					echo '<td>'.$serial_no.'</td>';
					echo '<td>'.$bank_name.'</td>';
					echo '<td>'.$transactor.'</td>';
					echo '<td>'.date("Y-m-d H:i:s",$time).'</td>';
					echo '<td>'.$money.'</td>';	
					echo '<td>'.$balance.'</td>';				
					echo '<td>'.$note.'</td>';
					echo '</tr>';
				}
			}
			mysql_close($db_link);
		}	
	}
	else if($sAction=="buyhistory")
	{
		$type 		= isset($_GET['type'])?$_GET['type']:'';
		$keyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
		
		if(strlen($keyword)<=0 || strlen($keyword)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		echo '<table width="800" border="0" class="dataintable">
				<tr>
					<th align="center" width="130">用户帐号</th>
					<th align="center" width="100">套餐名称</th>
					<th align="center" width="65">加速域名数</th>
					<th align="center" width="65">月度总流量</th>
					<th align="center" width="40">月份数</th>
					<th align="center" width="75">价格(元/月)</th>
					<th align="center" width="80">首月金额(元)</th>
					<th align="center" width="70">总金额(元)</th>
					<th align="center" width="55">余额(元)</th>
					<th align="center" width="80">购买日期</th> 
					<th align="center" width="80">到期日期</th>
					<th align="center" width="55">购买类型</th>
				</tr>';
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$keyword = mysql_real_escape_string($keyword); 
			$timeval = mysql_real_escape_string($timeval); 	
			
			if($timeval>1000) $timeval=1000;	
			
			if($type=="username")
			{
				if($timeval>0)
				{
					$timeval = (time()-$timeval*60*60*24);
					$sql = "SELECT * FROM fikcdn_buyhistory WHERE username='$keyword' AND time>'$timeval' ORDER BY id DESC;"; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_buyhistory WHERE username='$keyword' ORDER BY id DESC;"; 
				}
					
				$result = mysql_query($sql,$db_link);
				if($result)
				{	
					$row_count=mysql_num_rows($result);	
					for($i=0;$i<$row_count;$i++)
					{
						$id  			= mysql_result($result,$i,"id");
						$username  		= mysql_result($result,$i,"username");
						$buy_id	  		= mysql_result($result,$i,"buy_id");
						$price   		= mysql_result($result,$i,"price");	
						$month   		= mysql_result($result,$i,"month");
						$auto_renew		= mysql_result($result,$i,"auto_renew");
						$domain_num  	= mysql_result($result,$i,"domain_num");
						$data_flow  	= mysql_result($result,$i,"data_flow");
						$balance  		= mysql_result($result,$i,"balance");
						$buy_time   	= mysql_result($result,$i,"buy_time");
						$end_time   	= mysql_result($result,$i,"end_time");
						$buy_ip 		= mysql_result($result,$i,"ip");
						$type   		= mysql_result($result,$i,"type");
						$note   		= mysql_result($result,$i,"note");
						$frist_month_money  = mysql_result($result,$i,"frist_month_money");
						
						$total_money = ($price*($month-1))+$frist_month_money; 
						
						$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{
							$product_id  = mysql_result($result2,0,"product_id");
							
							$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
							$result2 = mysql_query($sql,$db_link);
							if($result2 && mysql_num_rows($result2)>0)
							{
								$product_name  = mysql_result($result2,0,"name");
								//$product_name = $product_name.'('.$buy_id.')';
							}
						}
										
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
						echo '<td>'.$username.'</td>';
						echo '<td>'.$product_name.'</td>';
						echo '<td>'.$domain_num.'</td>';
						echo '<td>'.PubFunc_MBToString($data_flow).'</td>';
						echo '<td>'.$month.'</td>';
						echo '<td>'.$price.'</td>';
						echo '<td>'.$frist_month_money.'</td>';
						echo '<td>'.$total_money.'</td>';
						echo '<td>'.$balance.'</td>';
						echo '<td>'.date("Y-m-d",$buy_time).'</td>';
						echo '<td>'.date("Y-m-d",$end_time).'</td>';
						echo '<td>'.$PubDefine_BuyTypeStr[$type].'</td>';//或继费
						echo '</tr>';
					}
				}
			}		
			else
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
		}				
	}
	else if($sAction=="buy")
	{
		$type 		= isset($_GET['type'])?$_GET['type']:'';
		$keyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
		
		if(strlen($keyword)<=0 || strlen($keyword)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		echo '<table width="800" border="0" class="dataintable">
				<tr>
					<th align="center" width="55">序号</th> 
					<th align="center" width="130">客户帐号</th>
					<th align="center" width="100">产品名称</th>
					<th align="center" width="55">域名个数</th>
					<th align="center" width="65">月度总流量</th>				
					<th align="center" width="75">价格(元/月)</th>
					<th align="center" width="80">开始日期</th> 
					<th align="center" width="80">到期日期</th>
					<th align="center" width="105" align="center">本月流量统计</th>
					<th align="center" width="105" align="center">本月请求量统计</th>				
					<th align="center" width="50">状态</th>				
					<th align="center">操作</th>
				</tr>';			
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$keyword = mysql_real_escape_string($keyword); 
			$timeval = mysql_real_escape_string($timeval); 	
			
			if($timeval>1000) $timeval=1000;	
			
			if($type=="username")
			{
				if($timeval>0)
				{
					$timeval = (time()-$timeval*60*60*24);
					$sql = "SELECT * FROM fikcdn_buy WHERE username='$keyword' AND time>'$timeval' ORDER BY id DESC;"; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_buy WHERE username='$keyword' ORDER BY id DESC;"; 
				}
					
				$result = mysql_query($sql,$db_link);
				if($result)
				{	
					$row_count=mysql_num_rows($result);	
					for($i=0;$i<$row_count;$i++)
					{
						$id  			= mysql_result($result,$i,"id");
						$username  		= mysql_result($result,$i,"username");
						$product_id	  	= mysql_result($result,$i,"product_id");
						$begin_time   	= mysql_result($result,$i,"begin_time");
						$end_time   	= mysql_result($result,$i,"end_time");
						$note   		= mysql_result($result,$i,"note");
						$status   		= mysql_result($result,$i,"status");
						$auto_renew		= mysql_result($result,$i,"auto_renew");				
						$price   		= mysql_result($result,$i,"price");	
						$domain_num  	= mysql_result($result,$i,"domain_num");
						$data_flow   	= mysql_result($result,$i,"data_flow");
						
						$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{
							$product_name  = mysql_result($result2,0,"name");
						}
							
						$sql = "SELECT * FROM domain_stat_product_month WHERE buy_id='$id' AND time=$timeval1";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{
							$RequestCount  	= mysql_result($result2,0,"RequestCount");
							$UploadCount  	= mysql_result($result2,0,"UploadCount");
							$DownloadCount  = mysql_result($result2,0,"DownloadCount");
							$IpCount  		= mysql_result($result2,0,"IpCount");
						}
									
						if(strlen($RequestCount)<=0) $RequestCount=0;
						if(strlen($UploadCount)<=0) $UploadCount=0;
						if(strlen($DownloadCount)<=0) $DownloadCount=0;
						if(strlen($IpCount)<=0) $IpCount=0;
						
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
						echo '<td>'.$id.'</td>';
						echo '<td>'.$username.'</td>';
						echo '<td>'.$product_name.'</td>';
						echo '<td>'.$domain_num.'</td>';
						echo '<td>'.FuncAdmin_MBToString($data_flow).'</td>';				
						echo '<td>'.$price.'</td>';
						echo '<td>'.date("Y-m-d",$begin_time).'</td>';
						echo '<td>'.date("Y-m-d",$end_time).'</td>';
						echo '<td>'.PubFunc_GBToString($DownloadCount).'</td>';
						echo '<td>'.$RequestCount.'</td>';				
						echo '<td>'.$PubDefine_HostStatus[$status]. '</td>';
						echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_DelBuyProduct('.$id.');" title="删除已售出的套餐">删除</a>&nbsp;
							<a href="stat_buy_product_day_download.php?buy_id='.$id.'">流量统计</a>&nbsp;</td>';
						echo '</tr>';						
					}
				}
			}		
			else
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
		}				
	}
	else if($sAction=="order")
	{
		$type 		= isset($_GET['type'])?$_GET['type']:'';
		$keyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
		
		if(strlen($keyword)<=0 || strlen($keyword)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		echo '<table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="55">序号</th> 
				<th align="center" width="130">客户帐号</th>
				<th align="center" width="100">产品名称</th>
				<th align="center" width="70">月度总流量</th>
				<th align="center" width="55">域名个数</th>
				<th align="center" width="75">价格(元/月)</th>
				<th align="center" width="75">购买月份数</th>
				<th align="center" width="80">首月金额(元)</th>
				<th align="center" width="70">总金额(元)</th>
				<th align="center" width="75">下单日期</th> 
				<th align="center" width="60">购买类型</th>
			
				<th align="center">操作</th>
			</tr>';			
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$keyword = mysql_real_escape_string($keyword); 
			$timeval = mysql_real_escape_string($timeval); 	
			
			if($timeval>1000) $timeval=1000;	
			
			if($type=="username")
			{
				if($timeval>0)
				{
					$timeval = (time()-$timeval*60*60*24);
					$sql = "SELECT * FROM fikcdn_order WHERE username='$keyword' AND time>'$timeval' ORDER BY id DESC;"; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_order WHERE username='$keyword' ORDER BY id DESC;"; 
				}
					
				$result = mysql_query($sql,$db_link);
				if($result)
				{	
					$row_count=mysql_num_rows($result);	
					for($i=0;$i<$row_count;$i++)
					{
						$id  			= mysql_result($result,$i,"id");
						$username  		= mysql_result($result,$i,"username");
						$product_id	  	= mysql_result($result,$i,"product_id");
						$buy_time   	= mysql_result($result,$i,"buy_time");
						$note   		= mysql_result($result,$i,"note");
						$status   		= mysql_result($result,$i,"status");
						$auto_renew		= mysql_result($result,$i,"auto_renew");				
						$price   		= mysql_result($result,$i,"price");	
						$type   		= mysql_result($result,$i,"type");
						$month   		= mysql_result($result,$i,"month");	
						$type   		= mysql_result($result,$i,"type");		
						$domain_num  	= mysql_result($result,$i,"domain_num");
						$data_flow   	= mysql_result($result,$i,"data_flow");
						$frist_month_money	= mysql_result($result,$i,"frist_month_money");
						
						$total_money = ($price*($month-1))+$frist_month_money;
						
						$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
						$result2 = mysql_query($sql,$db_link);
						if($result2 && mysql_num_rows($result2)>0)
						{
							$product_name  = mysql_result($result2,0,"name");
						}
										
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
						echo '<td>'.$id.'</td>';
						echo '<td>'.$username.'</td>';
						echo '<td>'.$product_name.'</td>';
						echo '<td>'.PubFunc_MBToString($data_flow).'</td>';
						echo '<td>'.$domain_num.'</td>';
						echo '<td>'.$price.'</td>';
						echo '<td>'.$month.'</td>';
						echo '<td>'.$frist_month_money.'</td>';
						echo '<td>'.$total_money.'</td>';
						echo '<td>'.date("Y-m-d",$buy_time).'</td>';
						echo '<td>'.$PubDefine_BuyTypeStr[$type].'</td>';
						//echo '<td>'.$note.'</td>';
						echo '<td><a href="order_modify.php?order_id='.$id.'">修改订单</a>&nbsp;
								<a href="javascript:void(0);" onclick="javescript:FikCdn_DelOrder('.$id.');" title="删除任务">删除</a></td>';
						echo '</tr>';
					}
				}
			}		
			else
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
		}				
	}			
}


?>
