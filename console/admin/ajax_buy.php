<?php
include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once('function_admin.php');

//是否登录
if(!FuncAdmin_IsLogin())
{
	$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoLogin);
	PubFunc_EchoJsonAndExit($aryResult,NULL);
}
$sMod 	 	 = isset($_GET['mod'])?$_GET['mod']:'';
$sAction 	 = isset($_GET['action'])?$_GET['action']:'';

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];
$admin_username 	= $_SESSION['fikcdn_admin_username'];

if($sMod=='buy')
{
	if($sAction=="del")
	{
		$buy_id = isset($_POST['buy_id'])?$_POST['buy_id']:'';
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($buy_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result )
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户购买的产品套餐失败，数据库查询错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			if(mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户购买的产品套餐失败，请先删除此套餐下的所有域名。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);			
			}
			
			$sql = "DELETE FROM fikcdn_buy WHERE id=$buy_id";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户购买的产品套餐失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}			
			
			$aryResult = array('Return'=>'True','order_id'=>$order_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除用户购买的产品套餐失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}
	}
	else if($sAction=="modify")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$buy_id = isset($_POST['buy_id'])?$_POST['buy_id']:'';
		$domain_num = isset($_POST['domain_num'])?$_POST['domain_num']:'';
		$data_flow = isset($_POST['data_flow'])?$_POST['data_flow']:'';
		$price = isset($_POST['price'])?$_POST['price']:'';
		$end_time = isset($_POST['end_time'])?$_POST['end_time']:'';
		$txtNote = isset($_POST['note'])?$_POST['note']:'';
		$sDnsCName = isset($_POST['cname'])?$_POST['cname']:'';	
		$auto_stop_buy = isset($_POST['auto_stop_buy'])?$_POST['auto_stop_buy']:'';
		
		if( !is_numeric($buy_id) || !is_numeric($auto_stop_buy) ||strlen($txtNote)>128 ||  !is_numeric($domain_num) ||  !is_numeric($data_flow) ||  !is_numeric($price) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($domain_num<=0 || $data_flow<=0 || $price<0 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($txtNote)>512 || strlen($end_time)>21 || strlen($sDnsCName) > 64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);		
		}
				
		if(!PubFunc_IsDigit($domain_num) || !PubFunc_IsDigit($data_flow))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
	
		$end_time = htmlspecialchars($end_time);
		$txtNote = htmlspecialchars($txtNote);
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$txtNote = mysql_real_escape_string($txtNote);
			$end_time = mysql_real_escape_string($end_time);
			
			$sql = "select * from fikcdn_buy where id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'修改套餐信息失败，套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$new_end_time = strtotime($end_time);
			
			$sql = "UPDATE fikcdn_buy SET price='$price',domain_num='$domain_num',end_time='$new_end_time',dns_cname='$sDnsCName',data_flow='$data_flow',auto_stop='$auto_stop_buy',note='$txtNote' WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'修改套餐信息失败，更新数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			
			$aryResult = array('Return'=>'True','buy_id'=>$buy_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改套餐信息失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
	else if($sAction=="startdomain")
	{
		$buy_id = isset($_POST['buy_id'])?$_POST['buy_id']:'';
		
		if( !is_numeric($buy_id) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'启动域名加速失败，套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,NULL);
			}
			
			$id  			= mysql_result($result,0,"id");
			$product_id	  	= mysql_result($result,0,"product_id");
			$end_time	  	= mysql_result($result,0,"end_time");
			$data_flow	  	= mysql_result($result,0,"data_flow");
			$auto_stop	  	= mysql_result($result,0,"auto_stop");									
			
			//查找这个套餐的所有域名，并且启用加速
			$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id'";
			$result10 = mysql_query($sql,$db_link);
			$row_count10 = mysql_num_rows($result10);
			if($result10 && $row_count10>0)
			{
				for($iii=0;$iii<$row_count10;$iii++)
				{
					$start_domain_id = mysql_result($result10,$iii,"id");
					$start_group_id = mysql_result($result10,$iii,"group_id");
					$start_hostname = mysql_result($result10,$iii,"hostname");
					$stop_status = mysql_result($result10,$iii,"status");
					
					if($stop_status==$PubDefine_HostStatusStop)
					{	
						//修改域名状态为启用
						$sql = "UPDATE fikcdn_domain SET status=$PubDefine_HostStatusOk WHERE id=$start_domain_id";
						$result21 = mysql_query($sql,$db_link);
						
						//删除还没有执行完成的任务
						$sql = "DELETE FROM fikcdn_task WHERE domain_id=$start_domain_id AND type=$PubDefine_TaskModifyDomainStatus";
						$result22 = mysql_query($sql,$db_link);
						
						$sql = "SELECT * FROM fikcdn_node WHERE groupid='$start_group_id'";	
						$result11 = mysql_query($sql,$db_link);
						$row_count11 = mysql_num_rows($result11);
						if($result11 && $row_count11>0)
						{
							for($kkk=0;$kkk<$row_count11;$kkk++)
							{
								$start_node_id	 = mysql_result($result11,$kkk,"id");
								$start_node_ip 		 = mysql_result($result11,$kkk,"ip");
								$start_node_password	 = mysql_result($result11,$kkk,"password");
								$start_node_admin_port = mysql_result($result11,$kkk,"admin_port");
								$start_node_auth_domain= mysql_result($result11,$kkk,"auth_domain");
								$start_node_SessionID	 = mysql_result($result11,$kkk,"SessionID");
								
								//加入后台任务
								$timenow2 = time();
								$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
								VALUES(NULL,'$admin_username',$PubDefine_TaskModifyDomainStatus,$timenow2,$start_domain_id,$start_node_id,$product_id,$buy_id,'$start_hostname',$start_group_id)";
								$result12 = mysql_query($sql,$db_link);
							}	
						}
					}
				}
			}
			
			$aryResult = array('Return'=>'True','buy_id'=>$buy_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'启动域名加速失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
	else if($sAction=="stopdomain")
	{
		$buy_id = isset($_POST['buy_id'])?$_POST['buy_id']:'';
		
		if( !is_numeric($buy_id) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'暂停域名加速失败，套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,NULL);
			}
			
			$id  			= mysql_result($result,0,"id");
			$product_id	  	= mysql_result($result,0,"product_id");
			$end_time	  	= mysql_result($result,0,"end_time");
			$data_flow	  	= mysql_result($result,0,"data_flow");
			$auto_stop	  	= mysql_result($result,0,"auto_stop");							
			
			//查找这个套餐的所有域名，并暂停加速
			$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id'";
			$result10 = mysql_query($sql,$db_link);
			$row_count10 = mysql_num_rows($result10);
			if($result10 && $row_count10>0)
			{
				for($iii=0;$iii<$row_count10;$iii++)
				{
					$start_domain_id = mysql_result($result10,$iii,"id");
					$start_group_id = mysql_result($result10,$iii,"group_id");
					$start_hostname = mysql_result($result10,$iii,"hostname");
					$stop_status = mysql_result($result10,$iii,"status");
					
					if($stop_status == $PubDefine_HostStatusOk)
					{
						//修改域名状态为暂停
						$sql = "UPDATE fikcdn_domain SET status=$PubDefine_HostStatusStop WHERE id=$start_domain_id";
						$result21 = mysql_query($sql,$db_link);
						
						//删除还没有执行完成的任务
						$sql = "DELETE FROM fikcdn_task WHERE domain_id=$start_domain_id AND type=$PubDefine_TaskModifyDomainStatus";
						$result22 = mysql_query($sql,$db_link);
						
						$sql = "SELECT * FROM fikcdn_node WHERE groupid='$start_group_id'";	
						$result11 = mysql_query($sql,$db_link);
						$row_count11 = mysql_num_rows($result11);
						if($result11 && $row_count11>0)
						{
							for($kkk=0;$kkk<$row_count11;$kkk++)
							{
								$start_node_id	 = mysql_result($result11,$kkk,"id");
								$start_node_ip 		 = mysql_result($result11,$kkk,"ip");
								$start_node_password	 = mysql_result($result11,$kkk,"password");
								$start_node_admin_port = mysql_result($result11,$kkk,"admin_port");
								$start_node_auth_domain= mysql_result($result11,$kkk,"auth_domain");
								$start_node_SessionID	 = mysql_result($result11,$kkk,"SessionID");
								
								//加入后台任务
								$timenow2 = time();
								$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
								VALUES(NULL,'$client_username',$PubDefine_TaskModifyDomainStatus,$timenow2,$start_domain_id,$start_node_id,$product_id,$buy_id,'$start_hostname',$start_group_id)";
								$result12 = mysql_query($sql,$db_link);
							}	
						}
					}
				}
			}
			
			$aryResult = array('Return'=>'True','buy_id'=>$buy_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);			
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'暂停域名加速失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}			
	}
}
else if($sMod=='order')
{
	if($sAction=="modify")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$order_id = isset($_POST['order_id'])?$_POST['order_id']:'';
		$domain_num = isset($_POST['domain_num'])?$_POST['domain_num']:'';
		$data_flow = isset($_POST['data_flow'])?$_POST['data_flow']:'';
		$price = isset($_POST['price'])?$_POST['price']:'';
		$month = isset($_POST['month'])?$_POST['month']:'';
		$txtNote = isset($_POST['note'])?$_POST['note']:'';
		
		if( !is_numeric($order_id) || strlen($txtNote)>128 ||  !is_numeric($domain_num) ||  !is_numeric($data_flow) ||  !is_numeric($price) || !is_numeric($month))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($domain_num<=0 || $data_flow<=0 || $price<0 || $month<=0 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(!PubFunc_IsDigit($domain_num) || !PubFunc_IsDigit($data_flow)|| !PubFunc_IsDigit($month))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
	
		$txtNote = htmlspecialchars($txtNote);
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$txtNote = mysql_real_escape_string($txtNote);
			
			$sql = "UPDATE fikcdn_order SET price='$price',month='$month',domain_num='$domain_num',data_flow='$data_flow',note='$txtNote' WHERE id='$order_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'修改订单信息失败，更新数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'True','order_id'=>$order_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改订单信息失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	}	
	else if($sAction=="del")
	{
		$order_id		= isset($_POST['order_id'])?$_POST['order_id']:'';
				
		if(!is_numeric($order_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
			
		//无权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无此操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "DELETE FROM fikcdn_order WHERE id=$order_id";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除用户订单失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			$aryResult = array('Return'=>'True','id'=>$order_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除用户订单失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
}

?>
