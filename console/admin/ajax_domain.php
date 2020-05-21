<?php
include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once("function_admin.php");

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];

//是否登录
if(!FuncAdmin_IsLogin())
{
	$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoLogin);
	PubFunc_EchoJsonAndExit($aryResult,NULL);
}
	
$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod=="domain")
{
	if($sAction=="add")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$sDomain 		= isset($_POST['domain'])?$_POST['domain']:'';
		$SSLOpt 		= isset($_POST['SSLOpt'])?$_POST['SSLOpt']:'';
		$SSLCrtContent	= isset($_POST['SSLCrtContent'])?$_POST['SSLCrtContent']:'';
		$SSLKeyContent 	= isset($_POST['SSLKeyContent'])?$_POST['SSLKeyContent']:'';
		$SSLExtraParams = isset($_POST['SSLExtraParams'])?$_POST['SSLExtraParams']:'';
		$sSrcip 		= isset($_POST['srcip'])?$_POST['srcip']:'';
		$sUnicomIP		= isset($_POST['unicom_ip'])?$_POST['unicom_ip']:'';
		$UpsSSLOpt		= isset($_POST['UpsSSLOpt'])?$_POST['UpsSSLOpt']:'';
		$sIcp 			= isset($_POST['icp'])?$_POST['icp']:'';
		$dns_name 		= isset($_POST['dns_name'])?$_POST['dns_name']:'';
		$sBackup		= isset($_POST['backup'])?$_POST['backup']:'';
		$buy_id			= isset($_POST['buy_id'])?$_POST['buy_id']:'';
		$upstream_add_type	=isset($_POST['upstream_add_type'])?$_POST['upstream_add_type']:''; 
		
		//去掉首尾的空格
		$sDomain = trim($sDomain);
		$sSrcip = trim($sSrcip);
		$sUnicomIP = trim($sUnicomIP);
		
		//域名全部用小写保存
		$sDomain =  strtolower($sDomain);
		
		//无添加域名权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($sUnicomIP) <=0 && strlen($sSrcip)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( !is_numeric($SSLOpt) || !is_numeric($UpsSSLOpt) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if(strlen($sDomain)<=0 || strlen($sDomain)>64 || strlen($sUnicomIP)>64|| strlen($sSrcip)>64 || !is_numeric($buy_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sBackup)>128 || strlen($sIcp)>32 || strlen($dns_name)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($upstream_add_type!=0 && $upstream_add_type!=1){
			$upstream_add_type = 0;
		}
	
		$sDomain = htmlspecialchars($sDomain);
		$sSrcip = htmlspecialchars($sSrcip); 
		$sUnicomIP = htmlspecialchars($sUnicomIP); 
		$sBackup = htmlspecialchars($sBackup); 
		$sIcp = htmlspecialchars($sIcp); 
		$dns_name = htmlspecialchars($dns_name);
		$SSLCrtContent = htmlspecialchars($SSLCrtContent); 
		$SSLKeyContent = htmlspecialchars($SSLKeyContent); 
		$SSLExtraParams = htmlspecialchars($SSLExtraParams);  
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sDomain = mysql_real_escape_string($sDomain);
			$sSrcip = mysql_real_escape_string($sSrcip); 
			$sUnicomIP = mysql_real_escape_string($sUnicomIP); 
			$sBackup = mysql_real_escape_string($sBackup);
			$sIcp = mysql_real_escape_string($sIcp); 
			$dns_name = mysql_real_escape_string($dns_name);
			$SSLCrtContent = mysql_real_escape_string($SSLCrtContent); 
			$SSLKeyContent = mysql_real_escape_string($SSLKeyContent); 
			$SSLExtraParams = mysql_real_escape_string($SSLExtraParams);  			
			
			//产品套餐
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'增加域名失败，购买的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			$domain_num  = mysql_result($result,0,"domain_num");
			$product_end_time 		= mysql_result($result,0,"end_time");
			$product_has_data_flow  = mysql_result($result,0,"has_data_flow");
			$product_id = mysql_result($result,0,"product_id");
			$product_username = mysql_result($result,0,"username");
			
			//查询用户
			$sql = "SELECT * FROM fikcdn_client WHERE username='$product_username'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'增加域名失败，此套餐的用户帐号不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			$domain_need_verify = mysql_result($result,0,"domain_need_verify");
			
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'增加域名失败，购买的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			$group_id = mysql_result($result,0,"group_id");
			$product_name = mysql_result($result,0,"name");
			
			$sql = "SELECT * FROM fikcdn_group WHERE id='$group_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'增加域名失败，产品套餐所在服务器组不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}						
			
			//域名是否已经存在
			$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$sDomain' AND group_id='$group_id'";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDomainHasExist,'ErrorMsg'=>'增加域名失败，此域名已经存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
			
			//增加域名数量			
			$sql = "SELECT count(*) FROM fikcdn_domain WHERE username='$client_username' AND buy_id='$buy_id'"; 
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'增加域名失败，查询域名个数错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
			$domain_count  = mysql_result($result,0,"count(*)");
			if($domain_count>=$domain_num)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDomainTooMore,'ErrorMsg'=>'增加域名失败，域名已达上限，不能再继续增加此套餐的域名。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			$timenow = time();
			
			//检查套餐是否过期
			if($timenow>$product_end_time)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDomainTooMore,'ErrorMsg'=>'增加域名失败，此套餐已经过期，不能再增加域名。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			if($domain_need_verify==1)
			{
				$domain_status=$PubDefine_HostStatusVerify;
			}
			else
			{
				$domain_status=$PubDefine_HostStatusOk;
			}
						
			$sql="INSERT INTO fikcdn_domain(id,hostname,username,add_time,buy_id,group_id,upstream,unicom_ip,icp,DNSName,status,begin_time,end_time,note,upstream_add_all,SSLOpt,SSLCrtContent,SSLKeyContent,SSLExtraParams,UpsSSLOpt) 
					VALUES(NULL,'$sDomain','$product_username',$timenow,$buy_id,$group_id,'$sSrcip','$sUnicomIP','$sIcp','$dns_name',$domain_status,0,0,'$sBackup',$upstream_add_type,'$SSLOpt','$SSLCrtContent','$SSLKeyContent','$SSLExtraParams','$UpsSSLOpt')";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'增加域名失败，插入数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			$insert_id = mysql_insert_id($db_link);
			
			// 域名不需要审核
			if($domain_status==$PubDefine_HostStatusOk)
			{
				//服务器组
				$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'增加域名失败，查询服务器错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
									VALUES(NULL,'$client_username',$PubDefine_TaskAddProxy,$timenow,$insert_id,$node_id,$product_id,$buy_id,'$sDomain',$group_id)";
					$result2 = mysql_query($sql,$db_link);
				}
			}			
			
			$show_product_name = $product_name.'('.$product_username.')';
			$aryResult = array('Return'=>'True','id'=>$insert_id,'domain'=>$sDomain,'SSLOpt'=>$SSLOpt,'UpsSSLOpt'=>$UpsSSLOpt,'upstream'=>$sSrcip,'unicom_ip'=>$sUnicomIP,'username'=>$product_username,'show_product_name'=>$show_product_name,'status'=>$domain_status,'note'=>$sBackup);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'增加域名失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
	else if($sAction=="modify")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$domain_id 		= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		$SSLOpt 		= isset($_POST['SSLOpt'])?$_POST['SSLOpt']:'';
		$SSLCrtContent	= isset($_POST['SSLCrtContent'])?$_POST['SSLCrtContent']:'';
		$SSLKeyContent 	= isset($_POST['SSLKeyContent'])?$_POST['SSLKeyContent']:'';
		$SSLExtraParams = isset($_POST['SSLExtraParams'])?$_POST['SSLExtraParams']:'';		
		$sSrcip 		= isset($_POST['srcip'])?$_POST['srcip']:'';
		$sUnicomIP		= isset($_POST['unicom_ip'])?$_POST['unicom_ip']:'';
		$UpsSSLOpt 		= isset($_POST['UpsSSLOpt'])?$_POST['UpsSSLOpt']:'';
		$nBuyId 		= isset($_POST['buy_id'])?$_POST['buy_id']:'';
		$sIcp 			= isset($_POST['icp'])?$_POST['icp']:'';
		$dns_name 		= isset($_POST['dns_name'])?$_POST['dns_name']:'';				
		$sBackup		= isset($_POST['backup'])?$_POST['backup']:'';
		$upstream_add_type	=isset($_POST['upstream_add_type'])?$_POST['upstream_add_type']:''; 
		
		$sSrcip = trim($sSrcip);
		$sUnicomIP = trim($sUnicomIP);
		
		//无修改权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}

		if(strlen($sUnicomIP) <=0 && strlen($sSrcip)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
			
		if( !is_numeric($SSLOpt) || !is_numeric($UpsSSLOpt) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
								
		if( strlen($sUnicomIP)>64|| strlen($sSrcip)>64 || !is_numeric($domain_id) || !is_numeric($nBuyId))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sBackup)>128 || strlen($sIcp)>32 || strlen($dns_name)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
	
		if($upstream_add_type!=0 && $upstream_add_type!=1){
			$upstream_add_type = 0;
		}
			
		$sSrcip = htmlspecialchars($sSrcip); 
		$sUnicomIP = htmlspecialchars($sUnicomIP); 
		$sBackup = htmlspecialchars($sBackup); 
		$sUsername = htmlspecialchars($sUsername); 
		$sIcp = htmlspecialchars($sIcp); 
		$dns_name = htmlspecialchars($dns_name); 		
		$SSLCrtContent = htmlspecialchars($SSLCrtContent); 
		$SSLKeyContent = htmlspecialchars($SSLKeyContent); 
		$SSLExtraParams = htmlspecialchars($SSLExtraParams);  
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sSrcip = mysql_real_escape_string($sSrcip);
			$sUnicomIP = mysql_real_escape_string($sUnicomIP); 
			$sBackup = mysql_real_escape_string($sBackup);
			$sUsername = mysql_real_escape_string($sUsername); 
			$sIcp = mysql_real_escape_string($sIcp); 
			$dns_name = mysql_real_escape_string($dns_name); 
			$SSLCrtContent = mysql_real_escape_string($SSLCrtContent); 
			$SSLKeyContent = mysql_real_escape_string($SSLKeyContent); 
			$SSLExtraParams = mysql_real_escape_string($SSLExtraParams);  
									
			$sql="SELECT * FROM fikcdn_domain WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名信息失败，您要修改的域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			$domain_group_id    = mysql_result($result,0,"group_id");
			$domain_username    = mysql_result($result,0,"username");
			$domain_hostname    = mysql_result($result,0,"hostname");
			$domain_buy_id	    = mysql_result($result,0,"buy_id");
			$domain_upstream    = mysql_result($result,0,"upstream");
			$domain_unicom_ip   = mysql_result($result,0,"unicom_ip");
			$domain_status		= mysql_result($result,0,"status");
			$upstream_add_all	= mysql_result($result,0,"upstream_add_all");
			$domain_SSLOpt 	    	= mysql_result($result,0,"SSLOpt");	
			$domain_SSLCrtContent	= mysql_result($result,0,"SSLCrtContent");			
			$domain_SSLKeyContent	= mysql_result($result,0,"SSLKeyContent");
			$domain_SSLExtraParams	= mysql_result($result,0,"SSLExtraParams");								
			$domain_UpsSSLOpt	    = mysql_result($result,0,"UpsSSLOpt");								
							
			//套餐必须是同一个组
			$sql = "SELECT * FROM fikcdn_buy WHERE id=$nBuyId" ;
			$result2 = mysql_query($sql,$db_link);
			if(!$result2 || mysql_num_rows($result2)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名信息失败，购买的套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			$product_id	= mysql_result($result2,0,"product_id");
			$domain_num	= mysql_result($result2,0,"domain_num");
			$buy_username	= mysql_result($result2,0,"username");	
			
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result2 = mysql_query($sql,$db_link);
			if(!$result2 || mysql_num_rows($result2)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名信息失败，购买的套餐产品不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
													
			$product_group_id	= mysql_result($result2,0,"group_id");
			
			//增加域名数量			
			if($domain_buy_id!=$nBuyId)
			{
				$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$nBuyId'";
				$result = mysql_query($sql,$db_link);
				if(!$result || mysql_num_rows($result)<=0)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名失败，查询域名个数错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);
				}	
				
				$domain_count  = mysql_result($result,0,"count(*)");
				if($domain_count>=$domain_num)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDomainTooMore,'ErrorMsg'=>'修改域名失败，域名已达上限，不能再继续增加此套餐的域名。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}	
			}					
						
			//如果源站IP无修改或域名还在审核中则不需要修改Fikker服务器
			if($domain_status==$PubDefine_HostStatusVerify)
			{
				$sql = "UPDATE fikcdn_domain SET upstream='$sSrcip',unicom_ip='$sUnicomIP',note='$sBackup',group_id='$product_group_id',upstream_add_all='$upstream_add_type',
							SSLOpt='$SSLOpt',SSLCrtContent='$SSLCrtContent',SSLKeyContent='$SSLKeyContent',SSLExtraParams='$SSLExtraParams',UpsSSLOpt='$UpsSSLOpt',
							buy_id=$nBuyId,username='$buy_username',icp='$sIcp',use_transit_node='$use_transit' WHERE id='$domain_id'";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名信息失败，更新数据库操作错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}
					
				$aryResult = array('Return'=>'True','id'=>$domain_id);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$domain_buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您域名所属的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			$old_product_id	= mysql_result($result,0,"product_id");
						
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_product WHERE id='$old_product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名信息失败，您购买的产品套餐已不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
			
			$old_product_name	= mysql_result($result,0,"name");
			$old_group_id 		= mysql_result($result,0,"group_id");
			
			$modify_upstream = 0;
			$modify_domain = 0;
			$modify_type = 0;
			
			if($sSrcip!=$upstream || $sUnicomIP!=$unicom_ip || $upstream_add_type!=$upstream_add_all)
			{
				$modify_upstream =1;
			}
			
			if($SSLOpt!=$domain_SSLOpt || $SSLCrtContent!=$domain_SSLCrtContent || $SSLKeyContent!=$domain_SSLKeyContent|| $SSLExtraParams!=$domain_SSLExtraParams || $UpsSSLOpt!=$domain_UpsSSLOpt)
			{
				$modify_domain = 1;
			}						
			
			if($modify_upstream ==1 || $modify_domain ==1 )
			{	
				if($modify_upstream==1 && $modify_domain ==1 )
				{
					$modify_type =1;
				}
				else if($modify_upstream==1)
				{
					$modify_type =2;
				}
				else if($modify_domain ==1)
				{
					$modify_type =3;
				}
				
				$sql = "SELECT * FROM fikcdn_node WHERE groupid='$product_group_id' AND is_close='0'";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名信息失败，查询服务器错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);
				}

				//删除还没执行完成的修改任务
				$sql = "DELETE FROM fikcdn_task WHERE domain_id=$domain_id AND type=$PubDefine_TaskModifyUpstream";
				$result2 = mysql_query($sql,$db_link);
				
				$node_count = mysql_num_rows($result);
				for($i=0;$i<$node_count;$i++)
				{							
					$node_id 		 = mysql_result($result,$i,"id");
					$node_ip 		 = mysql_result($result,$i,"ip");
					$node_password	 = mysql_result($result,$i,"password");
					$node_admin_port = mysql_result($result,$i,"admin_port");
					$node_auth_domain= mysql_result($result,$i,"auth_domain");
					$node_SessionID	 = mysql_result($result,$i,"SessionID");
					
					//添加修改任务，让后台去执行
					$timenow = time();
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,ext) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskModifyUpstream,$timenow,$domain_id,$node_id,$product_id,$nBuyId,'$domain_hostname',$product_group_id,'$modify_type')";
					$result2 = mysql_query($sql,$db_link);				
				}	
			}
			
			if($product_group_id!=$old_group_id )
			{				
				$sql = "SELECT * FROM fikcdn_node WHERE groupid='$old_product_id' AND is_close='0'";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名信息失败，查询服务器错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
					
					//添加删除任务，让后台去执行
					$timenow = time();
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskDelDomain,$timenow,$domain_id,$node_id,$old_product_id,$domain_buy_id,'$domain_hostname',$old_group_id)";
					$result2 = mysql_query($sql,$db_link);				
				}
			}
						
			$sql = "UPDATE fikcdn_domain SET upstream='$sSrcip',unicom_ip='$sUnicomIP',note='$sBackup',group_id='$product_group_id',upstream_add_all='$upstream_add_type',
						SSLOpt='$SSLOpt',SSLCrtContent='$SSLCrtContent',SSLKeyContent='$SSLKeyContent',SSLExtraParams='$SSLExtraParams',UpsSSLOpt='$UpsSSLOpt',
						buy_id=$nBuyId,username='$buy_username',icp='$sIcp' WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名信息失败，更新数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}

			$aryResult = array('Return'=>'True','id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);				
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改域名信息失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}	
	else if($sAction == "modifyset")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$domain_id 	= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		$offset 	= isset($_POST['offset'])?$_POST['offset']:'1';
		$upoffset	= isset($_POST['upoffset'])?$_POST['upoffset']:'1';
		$down_begin = isset($_POST['down_begin'])?$_POST['down_begin']:'0';
		$up_begin 	= isset($_POST['up_begin'])?$_POST['up_begin']:'0';
		
		//无修改权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
					
		if( !is_numeric($domain_id) || !is_numeric($offset) || !is_numeric($upoffset) || !is_numeric($down_begin) || !is_numeric($up_begin))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{						
			$sql = "UPDATE fikcdn_domain SET offset='$offset',upoffset='$upoffset',down_begin_val='$down_begin',up_begin_val='$up_begin' WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名信息失败，更新数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}

			$aryResult = array('Return'=>'True','id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);				
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改域名信息失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}			
	}
	else if($sAction == "del")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$domain_id 	= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		
		if( !is_numeric($domain_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		//无删除权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{				
			$sql="SELECT * FROM fikcdn_domain WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除域名失败，您要删除的域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			
			$hostname 		= mysql_result($result,0,"hostname");
			$username 		= mysql_result($result,0,"username");
			$buy_id			= mysql_result($result,0,"buy_id");
			$upstream		= mysql_result($result,0,"upstream");
			$status			= mysql_result($result,0,"status");
			$group_id		= mysql_result($result,0,"group_id");

			//还在审核中，可直接删除
			if($status==$PubDefine_HostStatusVerify)
			{
			 	$sql = "DELETE FROM fikcdn_domain WHERE id=$domain_id";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除域名失败，操作数据库错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);
				}
				
				$aryResult = array('Return'=>'True','id'=>$domain_id);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
						
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除域名失败，您域名所属的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			$product_id	= mysql_result($result,0,"product_id");
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除域名失败，您购买的产品套餐已不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$product_name	= mysql_result($result,0,"name");
			$group_id 		= mysql_result($result,0,"group_id");
			
			//删除还没执行完成的添加任务
			$sql = "DELETE FROM fikcdn_task WHERE domain_id=$domain_id AND type>=$PubDefine_TaskModifyUpstream AND type<=$PubDefine_TaskAddProxy";
			$result2 = mysql_query($sql,$db_link);		
						
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除域名失败，查询服务器错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
								VALUES(NULL,'$username',$PubDefine_TaskDelDomain,$timenow,$domain_id,$node_id,$product_id,$buy_id,'$hostname',$group_id)";
				$result2 = mysql_query($sql,$db_link);
			}			 						
						
			//删除域名
			$sql = "DELETE FROM fikcdn_domain WHERE id=$domain_id";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除域名失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			//删除源站
			$sql = "DELETE FROM fikcdn_upstream WHERE group_id='$group_id' AND hostname='$hostname';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrDel);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			
			$aryResult = array('Return'=>'True','id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除域名失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	}
	else if($sAction == "cleancache")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$group_id = isset($_POST['grp_id'])?$_POST['grp_id']:'';
		$url1 	= isset($_POST['url1'])?$_POST['url1']:'';
		$url2 	= isset($_POST['url2'])?$_POST['url2']:'';
		$url3 	= isset($_POST['url3'])?$_POST['url3']:'';
	
		$url1 = trim($url1);
		$url2 = trim($url2);
		$url3 = trim($url3);		
		
		if( strlen($url1)<=0 && strlen($url2)<=0 && strlen($url3)<=0 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( !is_numeric($group_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( strlen($url1)>1024 || strlen($url2)>1024 || strlen($url3)>1024 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{		
			$aryUrl = array();
			
			$url1 = mysql_real_escape_string($url1);
			$url2 = mysql_real_escape_string($url2);
			$url3 = mysql_real_escape_string($url3);
					
			$aryResult = array('Return'=>'True','id'=>$group_id);
				
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'清理缓存文件失败，服务器组不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
				
				//加入后台删除任务
				$timenow = time();
				
				if(strlen($url1)>0)
				{
					if(PubFunc_IsHomePage($url1))
					{
						$url1 = $url1.'/';
					}
					
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,ext) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskAdminClearCache,$timenow,0,$node_id,0,0,'',$group_id,'$url1')";
					$result2 = mysql_query($sql,$db_link);					
					if(!$result2)
					{

					}
				}
				
				if(strlen($url2)>0)
				{
					if(PubFunc_IsHomePage($url2))
					{
						$url2 = $url2.'/';
					}
									
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,ext) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskAdminClearCache,$timenow,0,$node_id,0,0,'',$group_id,'$url2')";
					$result2 = mysql_query($sql,$db_link);					
					if(!$result2)
					{

					}
				}
				
				if(strlen($url3)>0)
				{
					if(PubFunc_IsHomePage($url3))
					{
						$url3 = $url3.'/';
					}
					
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,ext) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskAdminClearCache,$timenow,0,$node_id,0,0,'',$group_id,'$url3')";
					$result2 = mysql_query($sql,$db_link);					
					if(!$result2)
					{

					}
				}
			}			
					
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'清理缓存文件失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}	
	else if($sAction == "cleandircache")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$group_id = isset($_POST['grp_id'])?$_POST['grp_id']:'';
		$url 	= isset($_POST['url'])?$_POST['url']:'';

		$url = trim($url);
		
		if( strlen($url)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( !is_numeric($group_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( strlen($url)>1024)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{			
			$url = mysql_real_escape_string($url);
						
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'清理缓存文件失败，服务器查询错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
				
				//加入后台删除任务
				$timenow = time();
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,ext) 
								VALUES(NULL,'$admin_username',$PubDefine_TaskDirClearCache,$timenow,0,$node_id,0,0,'',$group_id,'$url')";
				$result2 = mysql_query($sql,$db_link);					
				if(!$result2)
				{
					echo mysql_error($db_link).'<br />';
					echo $sql.'<br />';
				}
			}			
			
			$aryResult = array('Return'=>'True','gid'=>$group_id);
			
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'清理缓存文件失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
	else if($sAction=="start" || $sAction=="stop")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$domain_id 	= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		
		if( !is_numeric($domain_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$new_status = $PubDefine_HostStatusStop;
		
		if($sAction=="start")
		{
			$new_status = $PubDefine_HostStatusOk;
		}
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{				
			$sql="SELECT * FROM fikcdn_domain WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您要修改的域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			
			$hostname 		= mysql_result($result,0,"hostname");
			$username 		= mysql_result($result,0,"username");
			$buy_id			= mysql_result($result,0,"buy_id");
			$upstream		= mysql_result($result,0,"upstream");
			$status			= mysql_result($result,0,"status");
		
			//还在审核中，不能修改状态
			if($status==$PubDefine_HostStatusVerify)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'修改域名状态失败，域名还在审核中。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您域名所属的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			$product_id	= mysql_result($result,0,"product_id");
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您购买的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$product_name	= mysql_result($result,0,"name");
			$group_id 		= mysql_result($result,0,"group_id");
			
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名状态失败，查询服务器错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			//删除还没执行完成的添加任务
			$sql = "DELETE FROM fikcdn_task WHERE domain_id=$domain_id AND type=$PubDefine_TaskModifyDomainStatus";
			$result2 = mysql_query($sql,$db_link);
						
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
								VALUES(NULL,'$username',$PubDefine_TaskModifyDomainStatus,$timenow,$domain_id,$node_id,$product_id,$buy_id,'$hostname',$group_id)";
				$result2 = mysql_query($sql,$db_link);
			}			 	
			
			//修改域名状态
			$sql = "UPDATE fikcdn_domain SET status=$new_status WHERE id=$domain_id";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'修改域名状态失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'True','id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改域名状态失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	}
	else if($sAction=="verify")
	{
		$admin_username 	= $_SESSION['fikcdn_admin_username'];

		$domain_id 	= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		
		if( !is_numeric($domain_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{				
			$sql="SELECT * FROM fikcdn_domain WHERE id='$domain_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您要修改的域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}			
			
			$hostname 		= mysql_result($result,0,"hostname");
			$username 		= mysql_result($result,0,"username");
			$buy_id			= mysql_result($result,0,"buy_id");
			$upstream		= mysql_result($result,0,"upstream");
			$status			= mysql_result($result,0,"status");
		
			//审核已经通过
			if($status!=$PubDefine_HostStatusVerify)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'修改域名状态失败，域名已经审核通过。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您域名所属的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			$product_id	= mysql_result($result,0,"product_id");
			
			//查询产品所属服务器组
			$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改域名状态失败，您购买的产品套餐不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$product_name	= mysql_result($result,0,"name");
			$group_id 		= mysql_result($result,0,"group_id");
			
			//服务器组
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$group_id' AND is_close='0'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改域名状态失败，查询服务器错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
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
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'修改域名状态失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'True','id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改域名状态失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}	
	else if($sAction =="delnodedomain")
	{
		$id  	 = isset($_POST['id'])?$_POST['id']:'';
		$nodeid  = isset($_POST['nodeid'])?$_POST['nodeid']:'';
		$sDomain = isset($_POST['domain'])?$_POST['domain']:'';
		
		if(!is_numeric($id) || !is_numeric($nodeid))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$id,'nodeid'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sDomain)<=0 || strlen($sDomain)>128)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$id,'nodeid'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}			
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "SELECT * FROM fikcdn_node WHERE id='$nodeid'";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0);
			{
				$i=0;
				
				$nodeid  		= mysql_result($result,$i,"id");	
				$name   		= mysql_result($result,$i,"name");	
				$ip  		 	= mysql_result($result,$i,"ip");	
				$port   		= mysql_result($result,$i,"port");	
				$admin_port   	= mysql_result($result,$i,"admin_port");	
				$password   	= mysql_result($result,$i,"password");
				$SessionID   	= mysql_result($result,$i,"SessionID");	
				$auth_domain   	= mysql_result($result,$i,"auth_domain");
				$groupid	   	= mysql_result($result,$i,"groupid");
				$status	   		= mysql_result($result,$i,"status");		
						
				$nProxyID = 0;
				
				//获得主机列表的 ProxyID
				$aryQueryResult = fikapi_proxyquerydomain($ip,$admin_port,$SessionID,$sDomain);
				if($aryQueryResult["Return"]=="True")
				{
					$nProxyID = $aryQueryResult["ProxyID"];
				}
				else
				{
					if($aryQueryResult["ErrorNo"]==-2)
					{						
						if($aryQueryResult["FikErrorNo"]==$FikCacheError_SessionHasOverdue)
						{
							$aryRelogin = fikapi_relogin($nodeid,$ip,$admin_port,$password,$db_link);
							if($aryRelogin["Return"]=="True")
							{
								$SessionID = $aryRelogin["SessionID"];
								$aryQueryResult=fikapi_proxyquerydomain($ip,$admin_port,$SessionID,$sDomain,$sBackup);				
								if($aryQueryResult["Return"]=="True")
								{
									$nProxyID = $aryQueryResult["ProxyID"];
								}
							}
							else
							{			
								if($aryRelogin["ErrorNo"]==-1)
								{
									$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnFik,'id'=>$id,'nodeid'=>$nodeid);
									PubFunc_EchoJsonAndExit($aryResult,$db_link);							
								}
								else
								{
									$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrReturnFalse,'FikErrorNo'=>$aryRelogin["FikErrorNo"],'id'=>$id,'nodeid'=>$nodeid);
									PubFunc_EchoJsonAndExit($aryResult,$db_link);	
								}
							}
						}
					}
					else if($aryQueryResult["ErrorNo"]==-3)
					{
						$aryResult = array('Return'=>'true','id'=>$id,'nodeid'=>$nodeid,'ProxyID'=>$nProxyID);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);
					}
					else
					{
						$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnFik,'id'=>$id,'nodeid'=>$nodeid);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);	
					}
				}
				
				if($aryQueryResult["Return"]=="True")
				{
					$aryProxyDel=fikapi_proxydel($ip,$admin_port,$SessionID,$nProxyID);
					if($aryProxyDel["Return"]=="True")
					{
						$aryResult = array('Return'=>'true','id'=>$id,'nodeid'=>$nodeid,'ProxyID'=>$nProxyID);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);
					}
					else if($aryProxyDel["Return"]=="False")
					{
						$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrReturnFalse,'FikErrorNo'=>$aryProxyDel["ErrorNo"],'id'=>$id,'nodeid'=>$nodeid);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);	
					}
					else
					{				
						$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnFik,'id'=>$id,'nodeid'=>$nodeid);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);					
					}
				}
			}	
		}	
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB,'id'=>$id,'nodeid'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}
	else if($sAction=="focus")
	{
		$domain_id		= isset($_POST['domain_id'])?$_POST['domain_id']:'';
		$is_focus 		= isset($_POST['is_focus'])?$_POST['is_focus']:'';
		
		if(!is_numeric($domain_id) || !is_numeric($is_focus))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($is_focus!=0 && $is_focus!=1)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$domain_id);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "UPDATE fikcdn_domain SET is_focus='$is_focus' WHERE id='$domain_id';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrUpdate,'id'=>$domain_id);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'true','id'=>$domain_id,'is_focus'=>$is_focus);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB,'id'=>$domain_id);
			echo json_encode($aryResult);
		}		
	}
}
else if($sMod=="upstream")
{
	if($sAction=="add")
	{
		$node_id		= isset($_POST['node_id'])?$_POST['node_id']:'';
		$sDomain 		= isset($_POST['domain'])?$_POST['domain']:'';
		$sSrcip	 		= isset($_POST['srcip'])?$_POST['srcip']:'';
		$sSrcip2 		= isset($_POST['srcip'])?$_POST['srcip2']:'';
		$sBackup 		= isset($_POST['backup'])?$_POST['backup']:'';
		
		//去掉首尾的空格
		$sDomain = trim($sDomain);
		$sSrcip = trim($sSrcip);
		$sSrcip2 = trim($sSrcip2);
		
		//域名全部用小写保存
		$sDomain =  strtolower($sDomain);
				
		//无添加源站权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($node_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sSrcip)<=0 && strlen($sSrcip2)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if( strlen($sDomain)>64|| strlen($sDomain)<=0 || strlen($sSrcip)>64 || strlen($sSrcip2)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if(strlen($backup)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sDomain = htmlspecialchars($sDomain);
		$sSrcip = htmlspecialchars($sSrcip);
		$sSrcip2 = htmlspecialchars($sSrcip2);  
		$sBackup = htmlspecialchars($sBackup); 
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sDomain = mysql_real_escape_string($sDomain);
			$sSrcip = mysql_real_escape_string($sSrcip); 
			$sSrcip2 = mysql_real_escape_string($sSrcip2);  
			$sBackup = mysql_real_escape_string($sBackup);
			
			$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$sDomain'";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，源站记录已经存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，服务器不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$group_id	   	= mysql_result($result,0,"groupid");
			$node_ip	   	= mysql_result($result,0,"ip");
			$node_unicom_ip	   	= mysql_result($result,0,"unicom_ip");
			$node_name	   	= mysql_result($result,0,"name");
			
			$sFikIp = $node_ip;
			if(strlen($sFikIp)<=0)
			{
				$sFikIp = $node_unicom_ip; 
			}
							
			//查询域名是否存在
			$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$sDomain' AND group_id=$group_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，此域名不属于此服务器组或者域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}		
			
			$domain_id  = mysql_result($result,0,"id");
			$username  	= mysql_result($result,0,"username");
			$buy_id	   	= mysql_result($result,0,"buy_id");
			$status	   	= mysql_result($result,0,"status");
			if($status==$PubDefine_HostStatusVerify)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，域名还没验证通过。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			mysql_query("START TRANSACTION",$db_link);
			
			//插入到数据库中
			$sql = "INSERT INTO fikcdn_upstream(id,node_id,group_id,hostname,upstream,upstream2,note) VALUE(NULL,$node_id,$group_id,'$sDomain','$sSrcip','$sSrcip2','$sBackup')";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				mysql_query("ROLLBACK",$db_link);
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，插入数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			$trains_id = mysql_insert_id($db_link);
			
			//删除原来的修改任务
			$sql = "DELETE FROM fikcdn_task WHERE node_id=$node_id AND domain_id=$domain_id";
			$result2 = mysql_query($sql,$db_link);
			
			//增加一个修改源站的任务
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
							VALUES(NULL,'$username',$PubDefine_TaskModifyUpstream,$timenow,$domain_id,$node_id,0,$buy_id,'$sDomain',$group_id)";
			$result2 = mysql_query($sql,$db_link);			
			if(!$result)
			{
				mysql_query("ROLLBACK",$db_link);
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，插入数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			mysql_query("COMMIT",$db_link);
			
			$aryResult = array('Return'=>'True','id'=>$trains_id,'node_id'=>$node_id,'domain_id'=>$domain_id,'domain'=>$sDomain,'src_ip'=>$sSrcip,'fik_ip'=>$sFikIp,'node_name'=>$node_name,'note'=>$sBackup);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加自定义源站失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}	
	else if($sAction=="modify")
	{
		$upstream_id	= isset($_POST['id'])?$_POST['id']:'';
		$sSrcip	 		= isset($_POST['upstream'])?$_POST['upstream']:'';
		$sSrcip2	 	= isset($_POST['upstream2'])?$_POST['upstream2']:'';
		$sBackup 		= isset($_POST['backup'])?$_POST['backup']:'';
		
		$sSrcip = trim($sSrcip);
		$sSrcip2 = trim($sSrcip2);		
		
		//无添加源站权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($upstream_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sSrcip)<=0 && strlen($sSrcip2)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if( strlen($sSrcip)>64 || strlen($sSrcip2)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if(strlen($sBackup)>128)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sSrcip = htmlspecialchars($sSrcip); 
		$sBackup = htmlspecialchars($sBackup); 
		$sSrcip2 = htmlspecialchars($sSrcip2); 
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sSrcip = mysql_real_escape_string($sSrcip); 
			$sBackup = mysql_real_escape_string($sBackup);	
			$sSrcip2 = mysql_real_escape_string($sSrcip2);
			
			$sql = "SELECT * FROM fikcdn_upstream WHERE id='$upstream_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改服务器自定义源站失败，源站记录已经存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			$node_id   	= mysql_result($result,0,"node_id");	
			$group_id	= mysql_result($result,0,"group_id");
			$hostname	= mysql_result($result,0,"hostname");		
			$upstream   = mysql_result($result,0,"upstream");
			$upstream2   = mysql_result($result,0,"upstream2");
			
			//查询域名是否存在
			$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$hostname' AND group_id=$group_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改服务器自定义源站失败，域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}		
			
			$domain_id  = mysql_result($result,0,"id");
			$username  	= mysql_result($result,0,"username");
			$buy_id	   	= mysql_result($result,0,"buy_id");
			
			mysql_query("START TRANSACTION",$db_link);
			
			//插入到数据库中
			$sql = "UPDATE fikcdn_upstream SET upstream='$sSrcip',upstream2='$sSrcip2',note='$sBackup' WHERE id='$upstream_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				mysql_query("ROLLBACK",$db_link);
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			if($upstream!=$sSrcip || $upstream2 != $sSrcip2)
			{
				//删除原来的修改任务
				$sql = "DELETE FROM fikcdn_task WHERE node_id=$node_id AND domain_id=$domain_id";
				$result2 = mysql_query($sql,$db_link);
			
				//增加一个修改源站的任务
				$timenow = time();
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
								VALUES(NULL,'$username',$PubDefine_TaskModifyUpstream,$timenow,$domain_id,$node_id,0,$buy_id,'$hostname',$group_id)";
				$result2 = mysql_query($sql,$db_link);			
				if(!$result)
				{
					mysql_query("ROLLBACK",$db_link);
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加自定义源站失败，插入数据库错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}
			}
			mysql_query("COMMIT",$db_link);
			
			$aryResult = array('Return'=>'True','id'=>$upstream_id,'node_id'=>$node_id,'domain_id'=>$domain_id,'upstream'=>$sSrcip,'upstream2'=>$sSrcip2,'node'=>$sBackup);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加自定义源站失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}	
	else if($sAction=="del")
	{
		$upstream_id		= isset($_POST['id'])?$_POST['id']:'';
		
		//无删除源站权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($upstream_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_upstream WHERE id='$upstream_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除自定义源站失败，源站记录不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	

			$node_id   	= mysql_result($result,0,"node_id");	
			$group_id	= mysql_result($result,0,"group_id");
			$hostname	= mysql_result($result,0,"hostname");		
			$upstream   = mysql_result($result,0,"upstream");	
			$note   	= mysql_result($result,0,"note");	
			
			//查询域名是否存在
			$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$hostname' AND group_id=$group_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$sql = "DELETE FROM fikcdn_upstream WHERE id=$upstream_id";
				$result = mysql_query($sql,$db_link);	
			
				$aryResult = array('Return'=>'True','id'=>$upstream_id,'node_id'=>$node_id);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}		
			
			$domain_id  = mysql_result($result,0,"id");
			$username  	= mysql_result($result,0,"username");
			$buy_id	   	= mysql_result($result,0,"buy_id");					
			
			mysql_query("START TRANSACTION",$db_link);
			
			//删除原来的修改任务
			$sql = "DELETE FROM fikcdn_task WHERE node_id=$node_id AND domain_id=$domain_id";
			$result2 = mysql_query($sql,$db_link);
			
			//增加一个修改源站的任务
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
							VALUES(NULL,'$username',$PubDefine_TaskModifyUpstream,$timenow,$domain_id,$node_id,0,$buy_id,'$hostname',$group_id)";
			$result2 = mysql_query($sql,$db_link);			
			if(!$result)
			{
				mysql_query("ROLLBACK",$db_link);
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除自定义源站失败，插入数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$sql = "DELETE FROM fikcdn_upstream WHERE id=$upstream_id";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				mysql_query("ROLLBACK",$db_link);
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除自定义源站失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			mysql_query("COMMIT",$db_link);
			
			$aryResult = array('Return'=>'True','id'=>$upstream_id,'node_id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除自定义源站失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	}
	else if($sAction=="same_config")
	{
		$upstream_id		= isset($_POST['id'])?$_POST['id']:'';
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		if(!is_numeric($upstream_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_upstream WHERE id='$upstream_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'同步自定义源站到服务器失败，源站记录不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	

			$node_id   	= mysql_result($result,0,"node_id");	
			$group_id	= mysql_result($result,0,"group_id");
			$hostname	= mysql_result($result,0,"hostname");		
			$upstream   = mysql_result($result,0,"upstream");	
			$note   	= mysql_result($result,0,"note");	
			
			//查询域名是否存在
			$sql = "SELECT * FROM fikcdn_domain WHERE hostname='$hostname' AND group_id=$group_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'同步自定义源站到服务器失败，域名不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}		
			
			$domain_id  = mysql_result($result,0,"id");
			$username  	= mysql_result($result,0,"username");
			$buy_id	   	= mysql_result($result,0,"buy_id");			
					
			//增加一个修改源站的任务
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
							VALUES(NULL,'$username',$PubDefine_TaskModifyUpstream,$timenow,$domain_id,$node_id,0,$buy_id,'$hostname',$group_id)";
			$result2 = mysql_query($sql,$db_link);			
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'同步自定义源站到服务器失败，插入数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			$aryResult = array('Return'=>'True','id'=>$upstream_id,'node_id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'同步自定义源站到服务器失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	}
	else if($sAction=="query")
	{
		$upstream_id		= isset($_POST['id'])?$_POST['id']:'';
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		if(!is_numeric($upstream_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_upstream WHERE id='$upstream_id'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改源站失败，源站记录不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	

			$node_id   	= mysql_result($result,0,"node_id");
			$group_id	= mysql_result($result,0,"group_id");
			$hostname	= mysql_result($result,0,"hostname");		
			$upstream   = mysql_result($result,0,"upstream");
			$upstream2  = mysql_result($result,0,"upstream2");		
			$note   	= mysql_result($result,0,"note");
			
			$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'错误，服务器不存在');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}				
			
			$node_group_id	= mysql_result($result,0,"groupid");
			$node_name	   	= mysql_result($result,0,"name");
			$node_ip	   	= mysql_result($result,0,"ip");
			$node_unicom_ip	= mysql_result($result,0,"unicom_ip");
			
			if(strlen($node_ip)<=0)
			{
				$node_ip = $node_unicom_ip;
			}
			else
			{
				if(strlen($node_unicom_ip)>0)
				{
					$node_ip = $node_ip.';'.$node_unicom_ip;
				}
			}
			
			$aryResult = array('Return'=>'True','id'=>$upstream_id,'hostname'=>$hostname,'upstream'=>$upstream,'upstream2'=>$upstream2,'note'=>$note,'node_id'=>$node_id,'node_name'=>$node_name,'node_ip'=>$node_ip,'node_unicom_ip'=>$node_unicom_ip);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'同步自定义源站到服务器失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
}
else if($sMod=="task")
{
	if($sAction=="del")
	{
		$task_id		= isset($_POST['task_id'])?$_POST['task_id']:'';
				
		if(!is_numeric($task_id))
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
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除后台任务失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			$aryResult = array('Return'=>'True','id'=>$task_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除后台任务失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}
	else if($sAction=="reexecute")
	{
		$task_id		= isset($_POST['task_id'])?$_POST['task_id']:'';
				
		if(!is_numeric($task_id))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "UPDATE fikcdn_task SET execute_count=0,result_str='' WHERE id=$task_id";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'重新将后台任务加入执行队列失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			$aryResult = array('Return'=>'True','id'=>$task_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'重新将后台任务加入执行队列失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
	}	
	else if($sAction=="autodel")
	{
		$autodel		= isset($_POST['autodel'])?$_POST['autodel']:'';
				
		if(!is_numeric($autodel))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$timeset=time();
			$sql = "SELECT * FROM fikcdn_params where name='auto_del_task';";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$sql = "UPDATE fikcdn_params SET value='$autodel',time='$timeset' WHERE name='auto_del_task'";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'设置失败，操作数据库错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}
			}
			else
			{
				$sql = "INSERT INTO fikcdn_params(name,value,time) VALUES('auto_del_task','$autodel','$timeset')";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'设置失败，插入数据库错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}
			}
			
			$aryResult = array('Return'=>'True','autodel'=>$autodel);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'设置失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}
}

?>
