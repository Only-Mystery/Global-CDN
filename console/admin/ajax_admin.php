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

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];

$sMod  = isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod == "fiknode")
{
	if($sAction=="add")
	{
		$sGrpid 	= isset($_POST['grpid'])?$_POST['grpid']:'';
		$sync_node_id 	= isset($_POST['sync_node_id'])?$_POST['sync_node_id']:'';		
		$sIp 		= isset($_POST['ip'])?$_POST['ip']:'';
		$sUnicomIP 	= isset($_POST['unicom_ip'])?$_POST['unicom_ip']:'';
		$sName 		= isset($_POST['name'])?$_POST['name']:'';
		$allowbw    = isset($_POST['allowbw'])?$_POST['allowbw']:'';
		$sPort 		= isset($_POST['port'])?$_POST['port']:'';
		$sAdminport = isset($_POST['adminport'])?$_POST['adminport']:'';
		$sPasswd 	= isset($_POST['passwd'])?$_POST['passwd']:'';
		$sBackup 	= isset($_POST['backup'])?$_POST['backup']:'';
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($sIp)<=0 && strlen($sUnicomIP)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if(strlen($sIP)>64 || strlen($sGrpid)==0 || strlen($sUnicomIP)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sName)==0 || strlen($sName)>64 || !is_numeric($sAdminport) || !is_numeric($allowbw) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sPasswd)==0 || strlen($sPasswd)>64 || strlen($sBackup)>512)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加服务器失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sGrpid 	= mysql_real_escape_string($sGrpid); 
		$sync_node_id 	= mysql_real_escape_string($sync_node_id); 
		$sIp 		= mysql_real_escape_string($sIp); 
		$sUnicomIP 	= mysql_real_escape_string($sUnicomIP); 
		$sName 		= mysql_real_escape_string($sName);
		$sPasswd 	= mysql_real_escape_string($sPasswd);  
		$sBackup 	= mysql_real_escape_string($sBackup);  	
		
		// 是否重复添加节点
		if(strlen($sIp)>0)
		{
			$sql = "SELECT * FROM fikcdn_node WHERE ip='$sIp'";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{	
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'添加服务器失败，服务器已经存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
		}
		else
		{
			$sql = "SELECT * FROM fikcdn_node WHERE unicom_ip='$sUnicomIP'";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{	
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'添加服务器失败，服务器已经存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
		}
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$sGrpid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加服务器失败，您添加的服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		
		$this_group_name	 = mysql_result($result,0,"name");
		
		if($nIsTransit)
		{
			if(strlen($sUnicomIP)<=0 || strlen($sIp)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加服务器失败，中转服务器必须是双线。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
		}
		
		$sFikIP=$sIp;
		if(strlen($sFikIP)<=0)
		{
			$sFikIP=$sUnicomIP;
		}
		
		//登录Fikker
		$AryResult = FikApi_Login($sFikIP,$sAdminport,$sPasswd);
		$AryResult = json_decode($AryResult,true); 
		if (!is_array($AryResult))
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'添加服务器失败，请确保【主控 -》Fikker】这两个点之间的线路可连接。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		if($AryResult["Return"]!="True")
		{		
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'ErrorMsg'=>'添加服务器失败，Fikker 管理员密码错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$fik_version 		= $AryResult["Version"];
		$fik_VersionExt		= $AryResult["VersionExt"];		
		$fik_session 		= $AryResult["SessionID"];
		$fik_LastLoginTime 	= $AryResult["LastLoginTime"];
	
		//或者Fikker的认证信息
		$AryAuthResult = FikApi_GetAuth($sFikIP,$sAdminport,$fik_session);
		$AryAuthResult = json_decode($AryAuthResult,true); 
		if($AryAuthResult["Return"]!="True")
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'ErrorMsg'=>'添加服务器失败，获取 Fikker 授权信息错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$AuthStatus = $AryAuthResult["Status"];
		switch($AuthStatus)
		{
		case 0:
			$auth_domain = $AryAuthResult["Hardware"];
			break;
		case 1:
			$auth_domain = $AryAuthResult["Hardware"];
			break;
		case 10:
			$auth_domain = $AryAuthResult["Hardware"];
			break;
		case 11:
			$auth_domain = $AryAuthResult["Hardware"];
			break;
		case 20:
			$auth_domain = $AryAuthResult["Binding"];
			break;	
		case 21:
			$auth_domain = $AryAuthResult["Binding"];
			break;								
		}
		
		$add_time = time();
		
		//插入服务器
		$sql = "INSERT INTO fikcdn_node(id,name,ip,unicom_ip,admin_port,password,note,add_time,fik_version,SessionID,fik_LastLoginTime,auth_domain,groupid,version_ext,allow_bandwidth)
				VALUES(NULL,'$sName','$sIp','$sUnicomIP','$sAdminport','$sPasswd','$sBackup','$add_time','$fik_version','$fik_session','$fik_LastLoginTime','$auth_domain','$sGrpid','$fik_VersionExt','$allowbw')";
		if(!mysql_query($sql,$db_link))
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'添加服务器失败，数据库操作错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$node_id = mysql_insert_id($db_link);
		
		//增加
		$sql = "SELECT * FROM fikcdn_domain WHERE group_id=$sGrpid";
		$result2 = mysql_query($sql,$db_link);
		if($result2)
		{
			$row_count = mysql_num_rows($result2);
			for($i=0;$i<$row_count;$i++)
			{
				$this_domain_id	 = mysql_result($result2,$i,"id");
				$this_hostname	 = mysql_result($result2,$i,"hostname");
				$this_upstream	 = mysql_result($result2,$i,"upstream");
				$this_group_id	 = mysql_result($result2,$i,"group_id");
				$this_buy_id	 = mysql_result($result2,$i,"buy_id");
				$this_domain_note= mysql_result($result2,$i,"note");		
				
				//加入后台任务
				$timenow = time();
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
								VALUES(NULL,'$admin_username',$PubDefine_TaskAddProxy,$timenow,$this_domain_id,$node_id,0,$this_buy_id,'$this_hostname',$this_group_id)";
				$result3 = mysql_query($sql,$db_link);
			}
		}
		
		$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
		
		$show_version = $fik_VersionExt.'/'.$show_version;
		
		// 增加同步缓存规则的任务
		if(strlen($sync_node_id)>0)
		{
			$sql = "SELECT * FROM fikcdn_node WHERE id=$sync_node_id;";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{					
				$sync_node_id	= mysql_result($result,0,"id");	
				$sync_node_name = mysql_result($result,0,"name");	
				$sync_node_ip 	= mysql_result($result,0,"ip");
				$sync_unicom_ip = mysql_result($result,0,"unicom_ip");
				$sync_group_id 	= mysql_result($result,0,"groupid");
				
				//添加同步任务，让后台去执行
				$timenow = time();
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
								VALUES(NULL,'$admin_username','$PubDefine_SyncFCache','$timenow',0,'$sync_node_id',0,0,'',$sync_group_id,'','','$node_id')";
				$result2 = mysql_query($sql,$db_link);
				
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
								VALUES(NULL,'$admin_username','$PubDefine_SyncRCache','$timenow',0,'$sync_node_id',0,0,'',$sync_group_id,'','','$node_id')";
				$result2 = mysql_query($sql,$db_link);	
							
				$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
								VALUES(NULL,'$admin_username','$PubDefine_SyncRewrite','$timenow',0,'$sync_node_id',0,0,'',$sync_group_id,'','','$node_id')";
				$result2 = mysql_query($sql,$db_link);
															
			}
		}
		
		/*				
		//增加一个域名同步任务
		$timenow = time();
		$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
						VALUES(NULL,'$admin_username',$PubDefine_TaskAddAllDomain,$timenow,0,$node_id,0,0,'',$sGrpid)";
		$result2 = mysql_query($sql,$db_link);
		*/
		$aryResult = array('Return'=>'True','grpid'=>$sGrpid,'node_id'=>$node_id,'name'=>$sName,'ip'=>$sIp,'unicom_ip'=>$sUnicomIP,'grp_name'=>$this_group_name,'show_version'=>$show_version,'status'=>"启用中");
		PubFunc_EchoJsonAndExit($aryResult,$db_link);	
	}
	else if($sAction == "modify")
	{
		$nId		= isset($_POST['id'])?$_POST['id']:'';
		$sGrpid 	= isset($_POST['grpid'])?$_POST['grpid']:'';
		$sName 		= isset($_POST['name'])?$_POST['name']:'';
		$sIp 		= isset($_POST['ip'])?$_POST['ip']:'';
		$sUnicomIP 	= isset($_POST['unicom_ip'])?$_POST['unicom_ip']:'';	
		$allowbw    = isset($_POST['allowbw'])?$_POST['allowbw']:'';	
		$sAdminport = isset($_POST['adminport'])?$_POST['adminport']:'';
		$sPasswd 	= isset($_POST['passwd'])?$_POST['passwd']:'';
		$sBackup 	= isset($_POST['backup'])?$_POST['backup']:'';
		
		$admin_username 	= $_SESSION['fikcdn_admin_username'];
		
		//无修改服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($sIp)<=0 && strlen($sUnicomIP<=0))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if(strlen($sIP)>64 || strlen($sGrpid)==0 || strlen($sUnicomIP)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sName)==0 || strlen($sName)>64 || !is_numeric($sAdminport) || !is_numeric($nId) || !is_numeric($allowbw) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($sPasswd)==0 || strlen($sPasswd)>64 || strlen($sBackup)>512)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sIp 		= mysql_real_escape_string($sIp); 
			$sUnicomIP 	= mysql_real_escape_string($sUnicomIP); 			
			$sName 		= mysql_real_escape_string($sName);
			$sPasswd 	= mysql_real_escape_string($sPasswd);  
			$sBackup 	= mysql_real_escape_string($sBackup);  	
			
			// 是否重复添加节点
			if(strlen($sIp)>0)
			{
				$sql = "SELECT * FROM fikcdn_node WHERE ip='$sIp' AND port='$sPort' AND id!='$nId'";
				$result = mysql_query($sql,$db_link);
				if($result && mysql_num_rows($result)>0)
				{				
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'修改服务器信息错误，服务器已经存在');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);		
				}	
			}
			else
			{
				$sql = "SELECT * FROM fikcdn_node WHERE unicom_ip='$sUnicomIP' AND port='$sPort' AND id!='$nId'";
				$result = mysql_query($sql,$db_link);
				if($result && mysql_num_rows($result)>0)
				{				
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'修改服务器信息错误，服务器已经存在');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);		
				}	
			}
			
			//检查组是否存在
			$sql = "SELECT * FROM fikcdn_group WHERE id='$sGrpid'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{			
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改服务器信息错误，服务器组不存在');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}	
			
			if($nIsTransit)
			{
				if(strlen($sUnicomIP)<=0 || strlen($sIp)<=0)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加服务器失败，中转服务器必须是双线。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);
				}
			}
						
			$sFikIP=$sIp;
			if(strlen($sFikIP)<=0)
			{
				$sFikIP=$sUnicomIP;
			}
			
			//查询信息
			$sql = "SELECT * FROM fikcdn_node WHERE id='$nId'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<0)
			{			
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'修改服务器信息错误，您修改的服务器不存在');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}	
			$sOldIP			= mysql_result($result,0,"ip");
			$sOldUnicomIP	= mysql_result($result,0,"unicom_ip");
			$sOldPort		= mysql_result($result,0,"port");	
			$sOldAdminPort	= mysql_result($result,0,"admin_port");
			$sOldGroupID	= mysql_result($result,0,"groupid");
			
			//登录Fikker
			$AryResult = FikApi_Login($sFikIP,$sAdminport,$sPasswd);
			$AryResult = json_decode($AryResult,true); 
		
			if (!is_array($AryResult))
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'修改服务器信息错误，不能连接到服务器。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}
			
			if($AryResult["Return"]!="True")
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'FikErrorNo'=>$AryResult["ErrorNo"],'ErrorMsg'=>'修改服务器信息错误，服务器管理员登录错误。');				
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}
			
			$fik_version 		= $AryResult["Version"];
			$fik_VersionExt		= $AryResult["VersionExt"];
			$fik_session 		= $AryResult["SessionID"];
			$fik_LastLoginTime 	= $AryResult["LastLoginTime"];
		
			//或者Fikker的认证信息
			$AryAuthResult = FikApi_GetAuth($sFikIP,$sAdminport,$fik_session);
			$AryAuthResult = json_decode($AryAuthResult,true); 
			if($AryAuthResult["Return"]!="True")
			{			
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'FikErrorNo'=>$AryResult["ErrorNo"],'ErrorMsg'=>'修改服务器信息错误，服务器管理员登录错误。');				
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}
			
			$AuthStatus = $AryAuthResult["Status"];
			switch($AuthStatus)
			{
			case 0:
				$auth_domain = $AryAuthResult["Hardware"];
				break;
			case 1:
				$auth_domain = $AryAuthResult["Hardware"];
				break;
			case 10:
				$auth_domain = $AryAuthResult["Hardware"];
				break;
			case 11:
				$auth_domain = $AryAuthResult["Hardware"];
				break;
			case 20:
				$auth_domain = $AryAuthResult["Binding"];
				break;	
			case 21:
				$auth_domain = $AryAuthResult["Binding"];
				break;								
			}
			
			$add_time = time();
			
			//修改服务器信息
			$sql = "UPDATE fikcdn_node SET name='$sName',admin_port='$sAdminport',ip='$sIp',unicom_ip='$sUnicomIP',password='$sPasswd',note='$sBackup',fik_version='$fik_version',SessionID='$fik_session',fik_LastLoginTime='$fik_LastLoginTime',auth_domain='$auth_domain',groupid='$sGrpid',status=1,version_ext='$fik_VersionExt', allow_bandwidth='$allowbw' WHERE id='$nId'";
			if(!mysql_query($sql,$db_link))
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'修改服务器信息错误，更新数据库记录失败。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			//IP变动了，重新同步下所有域名配置
			if( ($sIp!=$sOldIP && strlen($sIp)>0) || (($sOldUnicomIP!=$sUnicomIP) && strlen($sUnicomIP)>0))
			{
				// 删除之前的任务
				$sql = "DELETE FROM fikcdn_task WHERE node_id=$nId AND type>='$PubDefine_TaskModifyUpstream' AND type<='$PubDefine_TaskAddProxy'";
				$result2 = mysql_query($sql,$db_link);
			
				$sql = "SELECT * FROM fikcdn_domain WHERE group_id=$sOldGroupID";
				$result2 = mysql_query($sql,$db_link);
				if($result2)
				{
					$row_count = mysql_num_rows($result2);
					for($i=0;$i<$row_count;$i++)
					{
						$this_domain_id	 = mysql_result($result2,$i,"id");
						$this_hostname	 = mysql_result($result2,$i,"hostname");
						$this_upstream	 = mysql_result($result2,$i,"upstream");
						$this_group_id	 = mysql_result($result2,$i,"group_id");
						$this_buy_id	 = mysql_result($result2,$i,"buy_id");
						$this_domain_note= mysql_result($result2,$i,"note");		
						
						//加入后台任务
						$timenow = time();
						$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
										VALUES(NULL,'$admin_username',$PubDefine_TaskAddProxy,$timenow,$this_domain_id,$nId,0,$this_buy_id,'$this_hostname',$this_group_id)";
						$result3 = mysql_query($sql,$db_link);
					}
				}
			}	
					
			$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$sGrpid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改服务器信息错误，连接数据库失败。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}			
	}	
	else if($sAction == "del")
	{
		$nId	= isset($_POST['id'])?$_POST['id']:'';
		$grpid 	= isset($_POST['grpid'])?$_POST['grpid']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(!is_numeric($nId) || !is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "DELETE FROM fikcdn_node WHERE id='$nId';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			//删除自定义源站配置
			$sql = "DELETE FROM fikcdn_upstream WHERE node_id='$nId';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);				
			}
			
			//删除任务
			$sql = "DELETE FROM fikcdn_task WHERE node_id='$nId';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}
			
			//删除同步任务
			$sql = "DELETE FROM fikcdn_task WHERE ext='$nId' AND type='$PubDefine_SyncFCache';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}	
			
			//删除同步任务
			$sql = "DELETE FROM fikcdn_task WHERE ext='$nId' AND type='$PubDefine_SyncRCache';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}	
			
			//删除同步任务
			$sql = "DELETE FROM fikcdn_task WHERE ext='$nId' AND type='$PubDefine_SyncRewrite';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除服务器失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}									
			
			$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$grpid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除服务器失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
	}	
	else if($sAction == "cleanhost")
	{
		$nId	= isset($_POST['id'])?$_POST['id']:'';
		$grpid 	= isset($_POST['grpid'])?$_POST['grpid']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(!is_numeric($nId) || !is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "SELECT * FROM fikcdn_node WHERE id='$nId'";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'清空服务器所有域名错误，服务器不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);		
			}	
			
			$sIp			= mysql_result($result,0,"ip");
			$sUnicomIP		= mysql_result($result,0,"unicom_ip");
			$sPort			= mysql_result($result,0,"port");	
			$sAdminport		= mysql_result($result,0,"admin_port");
			$this_password	= mysql_result($result,0,"password");
			$sSessionID		= mysql_result($result,0,"SessionID");
			
			$sFikIP=$sIp;
			if(strlen($sFikIP)<=0)
			{
				$sFikIP=$sUnicomIP;
			}
			
			//登录Fikker
			$AryResult = FikApi_ProxyClean($sFikIP,$sAdminport,$sSessionID);
			if($AryResult["Return"]=="False")
			{
				if($AryResult["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($nId,$sFikIP,$sAdminport,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						$AryResult = FikApi_ProxyClean($sFikIP,$sAdminport,$this_SessionID);			
						if($AryResult["Return"]=="True")
						{
							$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$grpid);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);
						}
						else
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'清空服务器所有域名错误，连接节点服务器失败。');
							PubFunc_EchoJsonAndExit($aryResult,$db_link);	
						}
					}
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'清空服务器所有域名错误，连接节点服务器失败。');
							PubFunc_EchoJsonAndExit($aryResult,$db_link);	
						}
						else
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'清空服务器所有域名错误，登录节点服务器失败。');
							PubFunc_EchoJsonAndExit($aryResult,$db_link);	
						}
							
						continue;
					}
				}
				else
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'FikErrorNo'=>$AryResult["ErrorNo"],'ErrorMsg'=>'清空服务器所有域名错误，服务器返回错误。');				
					PubFunc_EchoJsonAndExit($aryResult,$db_link);						
				}
			}
			else if($AryResult["Return"]=="True")
			{
				$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$grpid);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			else
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'清空服务器所有域名错误，连接节点服务器失败。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		

	}
	else if($sAction=="modifystatus")
	{
		$nId		= isset($_POST['id'])?$_POST['id']:'';
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';
		$is_close 	= isset($_POST['is_close'])?$_POST['is_close']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(!is_numeric($nId) || !is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		
		if($is_close!=0 && $is_close!=1)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "UPDATE fikcdn_node SET is_close='$is_close' WHERE id='$nId';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			
			// 当 $is_close =0 时，可能需要同步下域名
			
			$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$grpid,'is_close'=>$is_close);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB,'id'=>$nId,'grpid'=>$grpid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
	}
	else if($sAction=="focus")
	{
		$node_id		= isset($_POST['node_id'])?$_POST['node_id']:'';
		$is_focus 		= isset($_POST['is_focus'])?$_POST['is_focus']:'';
		
		if(!is_numeric($node_id) || !is_numeric($is_focus))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($is_focus!=0 && $is_focus!=1)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam,'id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "UPDATE fikcdn_node SET is_focus='$is_focus' WHERE id='$node_id';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrUpdate,'id'=>$node_id);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'true','id'=>$node_id,'is_focus'=>$is_focus);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB,'id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}
	else if($sAction=="reconfighost")
	{
		$nId	= isset($_POST['id'])?$_POST['id']:'';
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';
		
		if(!is_numeric($nId) || !is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			// 删除之前的任务
			$sql = "DELETE FROM fikcdn_task WHERE node_id=$nId AND type>='$PubDefine_TaskModifyUpstream' AND type<='$PubDefine_TaskAddProxy'";
			$result2 = mysql_query($sql,$db_link);

			$sql = "SELECT * FROM fikcdn_domain WHERE group_id=$grpid";
			$result2 = mysql_query($sql,$db_link);
			if($result2)
			{				
				$row_count = mysql_num_rows($result2);
				for($i=$row_count-1;$i>=0;$i--)
				{
					$this_domain_id	 = mysql_result($result2,$i,"id");
					$this_hostname	 = mysql_result($result2,$i,"hostname");
					$this_upstream	 = mysql_result($result2,$i,"upstream");
					$this_group_id	 = mysql_result($result2,$i,"group_id");
					$this_buy_id	 = mysql_result($result2,$i,"buy_id");
					$this_domain_note= mysql_result($result2,$i,"note");		
					
					//加入后台任务
					$timenow = time();
					$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id) 
									VALUES(NULL,'$admin_username',$PubDefine_TaskAddProxy,$timenow,$this_domain_id,$nId,0,$this_buy_id,'$this_hostname',$this_group_id)";
					$result3 = mysql_query($sql,$db_link);
				}
			}
			
			$aryResult = array('Return'=>'True','id'=>$nId,'grpid'=>$sGrpid,'task_count'=>$row_count);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'id'=>$nId,'grpid'=>$grpid,'ErrorMsg'=>'服务器配置同步任务提交失败，连接数据库失败。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}
}
else if($sMod == "fikgroup")
{
	if($sAction == "add")
	{
		$grpname 	= isset($_POST['grpname'])?$_POST['grpname']:'';
		$is_transit 	= isset($_POST['is_transit'])?$_POST['is_transit']:'0';
		
		if(strlen($grpname)<=0 || strlen($grpname)>64 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if($is_transit!=1 && $is_transit!=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
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
			$escape_grpname = mysql_real_escape_string($grpname); 
	
			$sql = "SELECT * FROM fikcdn_group WHERE name='$escape_grpname'"; 
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$id    = mysql_result($result,0,"id");	
				$name  = mysql_result($result,0,"name");	
				
				$aryResult = array('Return'=>'True','id'=>$id,'name'=>$name );
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
			
			$add_time = time();
			
			$sql = "INSERT INTO fikcdn_group(id,name,create_time,status,creator) VALUES(NULL,'$escape_grpname','$add_time',0,'$username')";
			$result = mysql_query($sql,$db_link);	
			if(!$result)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'创建新的服务器组失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,NULL);	
			}
			
			$grpid = mysql_insert_id($db_link);
			
			$aryResult = array('Return'=>'True','id'=>$grpid,'name'=>$grpname );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'创建新的服务器组失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
	}	
	else if($sAction == "modify")
	{
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';
		$newname 	= isset($_POST['newname'])?$_POST['newname']:'';
		
		if(strlen($newname)<=0 || strlen($newname)>64 || strlen($grpid)<=0 || !is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		/*
		if(!is_numeric($grpprice))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		*/
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$escape_grpname = mysql_real_escape_string($newname); 
	
			$sql = "SELECT * FROM fikcdn_group WHERE name='$escape_grpname'"; 
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$id    = mysql_result($result,0,"id");	
				$name  = mysql_result($result,0,"name");
				
				if($id != $grpid)
				{
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'创建新的服务器组失败，组名已经存在。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}	
			}
			
			$sql = "UPDATE fikcdn_group SET name='$escape_grpname' WHERE id='$grpid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'创建新的服务器组失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}
			
			$aryResult = array('Return'=>'True','id'=>$grpid,'name'=>$escape_grpname );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'创建新的服务器组失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);		
		}	
	}
	else if($sAction == "del")
	{
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';

		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
		
		if(!is_numeric($grpid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			//节点组里是否还有域名
			$sql = "SELECT * FROM fikcdn_domain WHERE group_id='$grpid';";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDelUsing,'ErrorMsg'=>'删除服务器组失败，请先删除此组内所有的域名后再删除组。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
					
			//节点组里是否绑定了产品套餐
			$sql = "SELECT * FROM fikcdn_product WHERE group_id='$grpid';";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDelUsing,'ErrorMsg'=>'删除服务器组失败，请先删除此组所绑定的套餐。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
								
			//节点组里是否还有节点
			$sql = "SELECT * FROM fikcdn_node WHERE groupid='$grpid';";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDelUsing,'ErrorMsg'=>'删除服务器组失败，请先删除此组内所有服务器后再删除组。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);	
			}
			else
			{
				$sql = "DELETE FROM fikcdn_group WHERE id='$grpid';";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{					
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDelUsing,'ErrorMsg'=>'删除服务器组失败，数据库操作错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}

				$aryResult = array('Return'=>'True','id'=>$grpid );
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
		}	
		else
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除服务器组失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}			
	}
	else if($sAction=="modifystatus")
	{
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';
		$status 	= isset($_POST['status'])?$_POST['status']:'';
		
		if(!is_numeric($grpid))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
			
		if($status!=0 && $status!=1)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{			
			$sql = "UPDATE fikcdn_group SET status='$status' WHERE id='$grpid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrUpdate);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'true','id'=>$grpid,'status'=>$status);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
	}		
}
else if($sMod == "user")
{
	if($sAction == "add")
	{		
		$sUsername 	= isset($_POST['username'])?$_POST['username']:'';
		$status 	= isset($_POST['status'])?$_POST['status']:'';
		$password 	= isset($_POST['password'])?$_POST['password']:'';
		$realname 	= isset($_POST['realname'])?$_POST['realname']:'';
		$compname 	= isset($_POST['compname'])?$_POST['compname']:'';
		$phone 		= isset($_POST['phone'])?$_POST['phone']:'';
		$qq 		= isset($_POST['qq'])?$_POST['qq']:'';
		$addr 		= isset($_POST['addr'])?$_POST['addr']:'';
		$sBackup 	= isset($_POST['backup'])?$_POST['backup']:'';
		$need_verify= isset($_POST['need_verify'])?$_POST['need_verify']:'';
	
		if(strlen($sUsername)<=0 || strlen($sUsername)>64 || strlen($password)!=32 || strlen($realname)<=0 || strlen($realname)>32)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($phone)<=0 || strlen($phone)>32)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($compname)>256 || strlen($qq)>32 || strlen($addr)>256 || strlen($sBackup)>512)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
					
		if(!is_numeric($status) || !is_numeric($need_verify))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'注册用户失败，数据库连接错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		$sUsername 	= mysql_real_escape_string($sUsername); 
		$password 	= mysql_real_escape_string($password);
		$realname 	= mysql_real_escape_string($realname);  
		$compname 	= mysql_real_escape_string($compname);  	
		$phone 		= mysql_real_escape_string($phone);  	
		$qq 		= mysql_real_escape_string($qq);  	
		$addr 		= mysql_real_escape_string($addr);  
		$sBackup 	= mysql_real_escape_string($sBackup);  	
			
		// 是否重复添加
		$sql = "SELECT * FROM fikcdn_client WHERE username='$sUsername'";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrHasExist,'ErrorMsg'=>'注册用户失败，用户帐号已经存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);		
		}	
		
		$add_time = time();
		$client_ip = $_SERVER["REMOTE_ADDR"]; 
		
		$sql = "INSERT INTO fikcdn_client(id,username,realname,password,enable_login,register_time,register_ip,addr,phone,company_name,qq,note,domain_need_verify) VALUES(NULL,'$sUsername',
				'$realname','$password','$status','$add_time','$client_ip','$addr','$phone','$compname','$qq','$sBackup','$need_verify');";
		$result = mysql_query($sql,$db_link);	
		if(!$result)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'注册用户失败，数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}	

		$aryResult = array('Return'=>'True','name'=>$sUsername );
		PubFunc_EchoJsonAndExit($aryResult,$db_link);		
	}
	else if($sAction == "del")
	{
		$uid		= isset($_POST['id'])?$_POST['id']:'';
		
		if(!is_numeric($uid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无此操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sql = "SELECT * FROM fikcdn_client WHERE id='$uid'";
			$result = mysql_query($sql,$db_link);
			if(!$result && mysql_num_rows($result)<=0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户失败，此用户不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}

			$del_username  = mysql_result($result,0,"username");	
					
			$sql = "SELECT * FROM fikcdn_buy WHERE username='$del_username'";
			$result = mysql_query($sql,$db_link);
			if(!$result )
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户失败，数据库查询错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			if(mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除用户失败，请先删除此用户购买的套餐和添加的域名。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);			
			}
			
			$sql = "DELETE FROM fikcdn_order WHERE username='$del_username';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除用户失败，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
					
			$sql = "DELETE FROM fikcdn_client WHERE id='$uid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrDel,'ErrorMsg'=>'删除用户失败，数据库删除操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'True','id'=>$uid );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}			
	}
	else if($sAction == "modify")
	{
		$uid 		= isset($_POST['id'])?$_POST['id']:'';
		$status 	= isset($_POST['status'])?$_POST['status']:'';
		$need_verify= isset($_POST['need_verify'])?$_POST['need_verify']:'';
		$realname 	= isset($_POST['realname'])?$_POST['realname']:'';
		$compname 	= isset($_POST['compname'])?$_POST['compname']:'';
		$phone 		= isset($_POST['phone'])?$_POST['phone']:'';
		$qq 		= isset($_POST['qq'])?$_POST['qq']:'';
		$addr 		= isset($_POST['addr'])?$_POST['addr']:'';
		$sBackup 	= isset($_POST['backup'])?$_POST['backup']:'';
		$sPasswd    = isset($_POST['passwd'])?$_POST['passwd']:'';
		
		//无权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($realname)<=0 || strlen($realname)>32)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($phone)<=0 || strlen($phone)>32)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(strlen($compname)>256 || strlen($qq)>32 || strlen($addr)>256 || strlen($sBackup)>512)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		if(!is_numeric($status) || !is_numeric($uid) || !is_numeric($need_verify) )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$realname 	= mysql_real_escape_string($realname);  	
			$phone	= mysql_real_escape_string($phone);  
			$qq	= mysql_real_escape_string($qq);  
			$addr	= mysql_real_escape_string($addr);  
			$compname	= mysql_real_escape_string($compname);  
			$sBackup	= mysql_real_escape_string($sBackup);  
			$sPasswd 	= mysql_real_escape_string($sPasswd);  	
		
			$sql = "UPDATE fikcdn_client SET enable_login=$status,realname='$realname',company_name='$compname',phone='$phone',qq='$qq',note='$sBackup',addr='$addr',domain_need_verify='$need_verify' WHERE id='$uid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改用户信息失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);				
			}
			
			if(strlen($sPasswd)==32)
			{
				$sql = "UPDATE fikcdn_client SET password='$sPasswd' WHERE id='$uid';";
				$result = mysql_query($sql,$db_link);
				if(!$result)
				{					
					$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改用户信息失败，操作数据库错误。');
					PubFunc_EchoJsonAndExit($aryResult,$db_link);	
				}
			}
			
			$aryResult = array('Return'=>'True','id'=>$nid );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改用户信息失败，连接数据库错误。');			
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}	
}
else if($sMod == "pull")
{
	if($sAction == "addpull")
	{
		$grpid 		= isset($_POST['grpid'])?$_POST['grpid']:'';
		$sUsername 	= isset($_POST['username'])?$_POST['username']:'';
		$url 		= isset($_POST['url'])?$_POST['url']:'';
		$offerurl 	= isset($_POST['offerurl'])?$_POST['offerurl']:'';
		
		if(!is_numeric($grpid))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
		
		if(strlen($sUsername)<=0 || strlen($sUsername)>64 || strlen($url)<=0 || strlen($url)>1024)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if(strlen($offerurl)>1024)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{	
			$sUsername 	= mysql_real_escape_string($sUsername); 
			$url 		= mysql_real_escape_string($url);
			$offerurl 	= mysql_real_escape_string($offerurl);
			
			//用户名是否存在
			$sql = "SELECT * FROM fikcdn_client WHERE username='$sUsername';";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{			
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrNoUser);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			//任务是否已经存在
			$sql = "SELECT * FROM fikcdn_pull WHERE url='$url' AND groupid='$grpid';";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{			
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrHasExist);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
					
			//增加任务
			$add_time = time();
			$sql = "INSERT INTO fikcdn_pull(id,username,url,refer_url,time,status,groupid) VALUES(NULL,'$sUsername','$url','$offerurl',$add_time,1,$grpid);";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrInsert);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}	
			
			$aryResult = array('Return'=>'true','username'=>$sUsername );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
	}
	else if($sAction == "delpull")
	{
		$pullid  = isset($_POST['id'])?$_POST['id']:'';
		if(!is_numeric($pullid))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
			
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "DELETE FROM fikcdn_pull WHERE id='$pullid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrDel);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'true','id'=>$pullid );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}	
	}
	else if($sAction == "modifystatus")
	{
		$pullid  = isset($_POST['id'])?$_POST['id']:'';
		$status  = isset($_POST['status'])?$_POST['status']:'';
		if(!is_numeric($pullid) || !is_numeric($status))
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($status!=0&&$status!=1)
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{			
			$sql = "UPDATE fikcdn_pull SET status=$status WHERE id='$pullid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrUpdate);
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'true','id'=>$pullid );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}			
	}
}
else if($sMod=="admin")
{
	if($sAction=="modify")
	{
		$nid 		= isset($_POST['id'])?$_POST['id']:'';
		$status 	= isset($_POST['status'])?$_POST['status']:'';
		$realname 	= isset($_POST['realname'])?$_POST['realname']:'';
		$phone 		= isset($_POST['phone'])?$_POST['phone']:'';
		$qq 		= isset($_POST['qq'])?$_POST['qq']:'';
		$addr 		= isset($_POST['addr'])?$_POST['addr']:'';
		$sBackup 	= isset($_POST['backup'])?$_POST['backup']:'';
	
		if(strlen($realname)<=0 || strlen($realname)>64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);			
		}

		if(strlen($phone)<=0 || strlen($phone)>32)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}		
		
		if(strlen($qq)<=0 || strlen($qq)>16)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}

		if(strlen($addr)>256 || strlen($sBackup)>256)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		if(!is_numeric($status) || !is_numeric($nid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误。');
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
			$nid = mysql_real_escape_string($nid);
			$realname 	= mysql_real_escape_string($realname);  	
			$phone	= mysql_real_escape_string($phone);  
			$qq	= mysql_real_escape_string($qq);  
			$addr	= mysql_real_escape_string($addr);  
			$sBackup	= mysql_real_escape_string($sBackup);  	
			
			$sql = "UPDATE fikcdn_admin SET enable=$status,nick='$realname',phone='$phone',qq='$qq',note='$sBackup',addr='$addr' WHERE id='$nid';";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改用户资料错误，数据库操作错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);					
			}
			
			$aryResult = array('Return'=>'True','id'=>$nid );
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改用户资料错误，连接数据库失败。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
	}
}
//echo "mod=".$sMod."&action=".$sAction;


?>
