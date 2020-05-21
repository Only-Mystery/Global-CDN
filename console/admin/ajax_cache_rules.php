<?php
session_start(); 

include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once("function_admin.php");

$sMod 	 	 = isset($_GET['mod'])?$_GET['mod']:'';
$sAction 	 = isset($_GET['action'])?$_GET['action']:'';

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];
$admin_username 	= $_SESSION['fikcdn_admin_username'];

if($sMod=='fcache')
{
	if($sAction == "list")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'刷新页面缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$node_id 	= mysql_real_escape_string($node_id); 	
	
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'刷新页面缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sFikIP = $ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $unicom_ip;
		}
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$admin_port,$SessionID);
		if($aryFikResult==false){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新页面缓存规则失败，不能连接到服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$SessionID = $aryRelogin["SessionID"];
					$aryFikResult=FikApi_FCacheList($sFikIP,$admin_port,$SessionID);
				}
			}
		}
		
		if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的
			$sql ="DELETE FROM cache_rule_fcache WHERE node_id='$node_id'";
			$result3 = mysql_query($sql,$db_link);
						
			$nNumOfLists = $aryFikResult["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult["Lists"][$k]["NO"];
				$Wid = $aryFikResult["Lists"][$k]["Wid"];
				$Url = $aryFikResult["Lists"][$k]["Url"];
				$Icase = $aryFikResult["Lists"][$k]["Icase"];
				$Rules = $aryFikResult["Lists"][$k]["Rules"];
				$Expire = $aryFikResult["Lists"][$k]["Expire"];
				$Unit = $aryFikResult["Lists"][$k]["Unit"];
				$Icookie = $aryFikResult["Lists"][$k]["Icookie"];
				$Olimit = $aryFikResult["Lists"][$k]["Olimit"];
				$IsDiskCache = $aryFikResult["Lists"][$k]["IsDiskCache"];
				$Note = $aryFikResult["Lists"][$k]["Note"];
				
				$sUrl = urlencode($Url);
				$sNote = urlencode($Note);	
				
				$sql = "INSERT INTO cache_rule_fcache(id,node_id,group_id,NO,Wid,Url,Icase,Rules,Expire,Unit,Icookie,Olimit,IsDiskCache,Note) 
							VALUES(NULL,'$node_id','$groupid','$NO','$Wid','$sUrl','$Icase','$Rules','$Expire','$Unit','$Icookie','$Olimit','$IsDiskCache','$sNote')";
				$result3 = mysql_query($sql,$db_link);
			}
			
			$aryResult = array('Return'=>'True','node_id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新页面缓存规则失败，服务器返回错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}	
	else if($sAction == "add")
	{
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$Url = isset($_POST['Url'])?$_POST['Url']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Rules = isset($_POST['Rules'])?$_POST['Rules']:'0';
		$Expire = isset($_POST['Expire'])?$_POST['Expire']:'';
		$Unit = isset($_POST['Unit'])?$_POST['Unit']:'0';
		$Icookie = isset($_POST['Icookie'])?$_POST['Icookie']:'1';
		$Olimit = isset($_POST['Olimit'])?$_POST['Olimit']:'0';
		$IsDiskCache = isset($_POST['IsDiskCache'])?$_POST['IsDiskCache']:'1';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($Url)<=0 || strlen($Url)>1024 || strlen($Expire)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sUrl = urlencode($Url);
		
		$gid 	= mysql_real_escape_string($gid); 
		$sUrl 	= mysql_real_escape_string($sUrl); 
				
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加页面缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加页面缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		
		$postAry = array();
		$postAry["Url"] = urlencode($Url);
		$postAry["Icase"] = $Icase;
		$postAry["Rules"] = $Rules;		
		$postAry["Expire"] = $Expire;		
		$postAry["Unit"] = $Unit;				
		$postAry["Icookie"] = $Icookie;				
		$postAry["Olimit"] = $Olimit;		
		$postAry["IsDiskCache"] = $IsDiskCache;		
		$postAry["Note"] = urlencode($Note);	
				
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_AddFCache','$timenow',0,'$node_id',0,0,'',$gid,'$sUrl','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}	
	else if($sAction == "modify")
	{
		$fid = isset($_POST['fid'])?$_POST['fid']:'';
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$Url = isset($_POST['Url'])?$_POST['Url']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Rules = isset($_POST['Rules'])?$_POST['Rules']:'0';
		$Expire = isset($_POST['Expire'])?$_POST['Expire']:'';
		$Unit = isset($_POST['Unit'])?$_POST['Unit']:'0';
		$Icookie = isset($_POST['Icookie'])?$_POST['Icookie']:'1';
		$Olimit = isset($_POST['Olimit'])?$_POST['Olimit']:'0';
		$IsDiskCache = isset($_POST['IsDiskCache'])?$_POST['IsDiskCache']:'1';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($Url)<=0 || strlen($Url)>1024 || strlen($Expire)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sUrl = urlencode($Url);
		
		$fid 	= mysql_real_escape_string($fid);
		$gid 	= mysql_real_escape_string($gid); 
		$sUrl 	= mysql_real_escape_string($sUrl);
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加页面缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_fcache WHERE id='$fid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加页面缓存失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$old_Url = mysql_result($result,0,"Url");		
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加页面缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		
		$sUrl = urlencode($Url);
		
		$postAry = array();
		$postAry["Url"] = urlencode($Url);
		$postAry["Icase"] = $Icase;
		$postAry["Rules"] = $Rules;		
		$postAry["Expire"] = $Expire;		
		$postAry["Unit"] = $Unit;				
		$postAry["Icookie"] = $Icookie;				
		$postAry["Olimit"] = $Olimit;		
		$postAry["IsDiskCache"] = $IsDiskCache;		
		$postAry["Note"] = urlencode($Note);	
		
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_ModifyFCache','$timenow',0,'$node_id',0,0,'',$gid,'$sUrl','$old_Url','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "del")
	{
		$fid = isset($_POST['fid'])?$_POST['fid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$fid 	= mysql_real_escape_string($fid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_fcache WHERE id='$fid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DelFCache','$timenow',0,'$node_id',0,0,'',$groupid,'$old_Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "up")
	{
		$fid = isset($_POST['fid'])?$_POST['fid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$fid 	= mysql_real_escape_string($fid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_fcache WHERE id='$fid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
			
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_UpFCache','$timenow',0,'$node_id',0,0,'',$groupid,'$old_Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "down")
	{
		$fid = isset($_POST['fid'])?$_POST['fid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$fid 	= mysql_real_escape_string($fid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_fcache WHERE id='$fid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除页面缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DownFCache','$timenow',0,'$node_id',0,0,'',$groupid,'$Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}	
	else if($sAction=="sync")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		$node_id2 = isset($_POST['node_id2'])?$_POST['node_id2']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'同步页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id2'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name2   		= mysql_result($result,0,"name");	
		$ip2  		 	= mysql_result($result,0,"ip");
		$unicom_ip2	 	= mysql_result($result,0,"unicom_ip");		
		$port2   		= mysql_result($result,0,"port");	
		$admin_port2   	= mysql_result($result,0,"admin_port");	
		$password2   	= mysql_result($result,0,"password");
		$SessionID2   	= mysql_result($result,0,"SessionID");	
		$auth_domain2   = mysql_result($result,0,"auth_domain");
		$groupid2	   	= mysql_result($result,0,"groupid");
		$status2	   	= mysql_result($result,0,"status");	
		
		//添加同步任务，让后台去执行
		$timenow = time();
		$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
						VALUES(NULL,'$admin_username','$PubDefine_SyncFCache','$timenow',0,'$node_id',0,0,'',$groupid,'','','$node_id2')";
		$result2 = mysql_query($sql,$db_link);		
		
		$aryResult = array('Return'=>'True','node_id'=>$node_id,'node_id2'=>$node_id2);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);							
	}	
}
else if($sMod=='rcache')
{
	if($sAction == "list")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'刷新拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$node_id 	= mysql_real_escape_string($node_id); 	
	
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'刷新拒绝缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sFikIP = $ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $unicom_ip;
		}
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$admin_port,$SessionID);
		if($aryFikResult==false){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新拒绝缓存规则失败，不能连接到服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$SessionID = $aryRelogin["SessionID"];
					$aryFikResult=FikApi_RCacheList($sFikIP,$admin_port,$SessionID);
				}
			}
		}
		
		if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的
			$sql ="DELETE FROM cache_rule_rcache WHERE node_id='$node_id'";
			$result3 = mysql_query($sql,$db_link);
			
			$nNumOfLists = $aryFikResult["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult["Lists"][$k]["NO"];
				$Wid = $aryFikResult["Lists"][$k]["Wid"];
				$Url = $aryFikResult["Lists"][$k]["Url"];
				$Icase = $aryFikResult["Lists"][$k]["Icase"];
				$Rules = $aryFikResult["Lists"][$k]["Rules"];
				$Olimit = $aryFikResult["Lists"][$k]["Olimit"];
				$CacheLocation = $aryFikResult["Lists"][$k]["CacheLocation"];
				$Note = $aryFikResult["Lists"][$k]["Note"];
				
				$sUrl = urlencode($Url);
				$sNote = urlencode($Note);	
				
				$sql = "INSERT INTO cache_rule_rcache(id,node_id,group_id,NO,Wid,Url,Icase,Rules,Olimit,CacheLocation,Note) 
							VALUES(NULL,'$node_id','$groupid','$NO','$Wid','$sUrl','$Icase','$Rules','$Olimit','$CacheLocation','$sNote')";
				$result3 = mysql_query($sql,$db_link);
			}
			
			$aryResult = array('Return'=>'True','node_id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新拒绝缓存规则失败，服务器返回错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}
	else if($sAction == "add")
	{
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$Url = isset($_POST['Url'])?$_POST['Url']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Rules = isset($_POST['Rules'])?$_POST['Rules']:'0';
		$Olimit = isset($_POST['Olimit'])?$_POST['Olimit']:'0';
		$CacheLocation = isset($_POST['CacheLocation'])?$_POST['CacheLocation']:'1';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($Url)<=0 || strlen($Url)>1024 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sUrl = urlencode($Url);
				
		$gid 	= mysql_real_escape_string($gid); 
		$sUrl 	= mysql_real_escape_string($sUrl); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加拒绝缓存规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加拒绝缓存规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$postAry = array();
		$postAry["Url"] = urlencode($Url);
		$postAry["Icase"] = $Icase;
		$postAry["Rules"] = $Rules;			
		$postAry["Olimit"] = $Olimit;		
		$postAry["CacheLocation"] = $CacheLocation;		
		$postAry["Note"] = urlencode($Note);
		
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_AddRCache','$timenow',0,'$node_id',0,0,'',$gid,'$sUrl','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);		
	}	
	else if($sAction == "modify")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$Url = isset($_POST['Url'])?$_POST['Url']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Rules = isset($_POST['Rules'])?$_POST['Rules']:'0';
		$Expire = isset($_POST['Expire'])?$_POST['Expire']:'';
		$Unit = isset($_POST['Unit'])?$_POST['Unit']:'0';
		$Icookie = isset($_POST['Icookie'])?$_POST['Icookie']:'1';
		$Olimit = isset($_POST['Olimit'])?$_POST['Olimit']:'0';
		$IsDiskCache = isset($_POST['IsDiskCache'])?$_POST['IsDiskCache']:'1';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($Url)<=0 || strlen($Url)>1024)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sUrl = urlencode($Url);
		
		$rid 	= mysql_real_escape_string($rid);
		$gid 	= mysql_real_escape_string($gid); 
		$sUrl 	= mysql_real_escape_string($sUrl); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改拒绝缓存规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rcache WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改拒绝缓存规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$old_Url = mysql_result($result,0,"Url");
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改拒绝缓存规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		
		$postAry = array();
		$postAry["Url"] = urlencode($Url);
		$postAry["Icase"] = $Icase;
		$postAry["Rules"] = $Rules;					
		$postAry["Olimit"] = $Olimit;		
		$postAry["CacheLocation"] = $CacheLocation;		
		$postAry["Note"] = urlencode($Note);	
		
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_ModifyRCache','$timenow',0,'$node_id',0,0,'',$gid,'$sUrl','$old_Url','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "del")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$rid 	= mysql_real_escape_string($rid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除拒绝缓存规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rcache WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除拒绝缓存规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除拒绝缓存规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DelRCache','$timenow',0,'$node_id',0,0,'',$groupid,'$old_Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "up")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'上移拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$rid 	= mysql_real_escape_string($rid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移拒绝缓存规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rcache WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移拒绝缓存规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
			
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移拒绝缓存规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_UpRCache','$timenow',0,'$node_id',0,0,'',$groupid,'$old_Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "down")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'下移拒绝缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$fid 	= mysql_real_escape_string($fid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移拒绝缓存规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rcache WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移拒绝缓存规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$Url = mysql_result($result,0,"Url");
		$old_Url = ($Url);
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移拒绝缓存规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DownRCache','$timenow',0,'$node_id',0,0,'',$groupid,'$old_Url','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction=="sync")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		$node_id2 = isset($_POST['node_id2'])?$_POST['node_id2']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'同步页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id2'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name2   		= mysql_result($result,0,"name");	
		$ip2  		 	= mysql_result($result,0,"ip");
		$unicom_ip2	 	= mysql_result($result,0,"unicom_ip");		
		$port2   		= mysql_result($result,0,"port");	
		$admin_port2   	= mysql_result($result,0,"admin_port");	
		$password2   	= mysql_result($result,0,"password");
		$SessionID2   	= mysql_result($result,0,"SessionID");	
		$auth_domain2   = mysql_result($result,0,"auth_domain");
		$groupid2	   	= mysql_result($result,0,"groupid");
		$status2	   	= mysql_result($result,0,"status");	
		
		//添加同步任务，让后台去执行
		$timenow = time();
		$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
						VALUES(NULL,'$admin_username','$PubDefine_SyncRCache','$timenow',0,'$node_id',0,0,'',$groupid,'','','$node_id2')";
		$result2 = mysql_query($sql,$db_link);		
		
		$aryResult = array('Return'=>'True','node_id'=>$node_id,'node_id2'=>$node_id2);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);							
	}				
}
else if($sMod=='rewrite')
{
	if($sAction == "list")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'刷新页面缓存规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$node_id 	= mysql_real_escape_string($node_id); 	
	
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'刷新页面缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sFikIP = $ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $unicom_ip;
		}
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$admin_port,$SessionID);
		if($aryFikResult==false){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新页面缓存规则失败，不能连接到服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$admin_port,$password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$SessionID = $aryRelogin["SessionID"];
					$aryFikResult=FikApi_RewriteList($sFikIP,$admin_port,$SessionID);
				}
			}
		}
		
		if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的
			$sql ="DELETE FROM cache_rule_rewrite WHERE node_id='$node_id'";
			$result3 = mysql_query($sql,$db_link);
			
			$nNumOfLists = $aryFikResult["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult["Lists"][$k]["NO"];
				$RewriteID = $aryFikResult["Lists"][$k]["RewriteID"];
				$SourceUrl = $aryFikResult["Lists"][$k]["SourceUrl"];
				$DestinationUrl = $aryFikResult["Lists"][$k]["DestinationUrl"];
				$Icase = $aryFikResult["Lists"][$k]["Icase"];
				$Flag = $aryFikResult["Lists"][$k]["Flag"];
				$Note = $aryFikResult["Lists"][$k]["Note"];
				
				$sSourceUrl = urlencode($SourceUrl);
				$sDestinationUrl = urlencode($DestinationUrl);	
				$sNote = urlencode($Note);
				
				$sql = "INSERT INTO cache_rule_rewrite(id,node_id,group_id,NO,RewriteID,SourceUrl,DestinationUrl,Icase,Flag,Note) 
							VALUES(NULL,'$node_id','$groupid','$NO','$RewriteID','$sSourceUrl','$sDestinationUrl','$Icase','$Flag','$sNote')";
				$result3 = mysql_query($sql,$db_link);
			}
			
			$aryResult = array('Return'=>'True','node_id'=>$node_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);	
		}
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'ErrorMsg'=>'刷新页面缓存规则失败，服务器返回错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	}
	else if($sAction == "add")
	{
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$SourceUrl = isset($_POST['SourceUrl'])?$_POST['SourceUrl']:'';
		$DestinationUrl = isset($_POST['DestinationUrl'])?$_POST['DestinationUrl']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Flag = isset($_POST['Flag'])?$_POST['Flag']:'0';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($SourceUrl)<=0 || strlen($SourceUrl)>1024 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加转向规则缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sSourceUrl = urlencode($SourceUrl);
		$sDestinationUrl = urlencode($DestinationUrl);
				
		$gid 	= mysql_real_escape_string($gid); 
		$sSourceUrl 	= mysql_real_escape_string($sSourceUrl); 
		$sSourceUrl = mysql_real_escape_string($sSourceUrl); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加转向规则缓存失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加转向规则缓存失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		
		$postAry = array();
		$postAry["SourceUrl"] = urlencode($SourceUrl);
		$postAry["DestinationUrl"] = urlencode($DestinationUrl);
		$postAry["Icase"] = $Icase;
		$postAry["Flag"] = $Flag;				
		$postAry["Note"] = urlencode($Note);	
		
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_AddRewrite','$timenow',0,'$node_id',0,0,'',$gid,'$sSourceUrl','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);		
	}	
	else if($sAction == "modify")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$gid = isset($_POST['gid'])?$_POST['gid']:'';
		$SourceUrl = isset($_POST['SourceUrl'])?$_POST['SourceUrl']:'';
		$DestinationUrl = isset($_POST['DestinationUrl'])?$_POST['DestinationUrl']:'';
		$Icase = isset($_POST['Icase'])?$_POST['Icase']:'1';
		$Flag = isset($_POST['Flag'])?$_POST['Flag']:'0';
		$Note = isset($_POST['Note'])?$_POST['Note']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(strlen($SourceUrl)<=0 || strlen($SourceUrl)>1024)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改转向规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		
		
		$sSourceUrl = urlencode($SourceUrl);
		$sDestinationUrl = urlencode($DestinationUrl);		
		
		$rid 	= mysql_real_escape_string($rid);
		$gid 	= mysql_real_escape_string($gid); 
		$sSourceUrl 	= mysql_real_escape_string($sSourceUrl); 
		$sDestinationUrl 	= mysql_real_escape_string($sDestinationUrl); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_group WHERE id='$gid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改转向规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rewrite WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改转向规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$old_SourceUrl = mysql_result($result,0,"SourceUrl");
		$old_SourceUrl = ($old_SourceUrl);
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$gid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);			
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'修改转向规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
		
		$postAry = array();
		$postAry["SourceUrl"] = urlencode($SourceUrl);
		$postAry["DestinationUrl"] = urlencode($DestinationUrl);
		$postAry["Icase"] = $Icase;
		$postAry["Flag"] = $Flag;				
		$postAry["Note"] = urlencode($Note);
		
		$strJson = json_encode($postAry,true); 												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++)
		{
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_ModifyRewrite','$timenow',0,'$node_id',0,0,'',$gid,'$sSourceUrl','$old_SourceUrl','$strJson')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','gid'=>$gid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "del")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除转向规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$rid 	= mysql_real_escape_string($rid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除转向规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rewrite WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除转向规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$SourceUrl = mysql_result($result,0,"SourceUrl");
		$old_SourceUrl = ($SourceUrl);
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'删除转向规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DelRewrite','$timenow',0,'$node_id',0,0,'',$groupid,'$old_SourceUrl','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "up")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'上移转向规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$rid 	= mysql_real_escape_string($rid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移转向规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rewrite WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移转向规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$SourceUrl = mysql_result($result,0,"SourceUrl");
		$old_SourceUrl = ($SourceUrl);
			
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'上移转向规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_UpRewrite','$timenow',0,'$node_id',0,0,'',$groupid,'$old_SourceUrl','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction == "down")
	{
		$rid = isset($_POST['rid'])?$_POST['rid']:'';
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'下移转向规则失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$fid 	= mysql_real_escape_string($fid);
		$node_id = mysql_real_escape_string($node_id); 
		
		//检查组是否存在
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移转向规则失败，服务器组不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		$groupid = mysql_result($result,0,'groupid');
		
		//检查规则是否存在
		$sql = "SELECT * FROM cache_rule_rewrite WHERE id='$rid'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移转向规则失败，修改的页面缓存规则不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		$SourceUrl = mysql_result($result,0,"SourceUrl");
		$old_SourceUrl = ($SourceUrl);
			
		 		
		$sql = "SELECT * FROM fikcdn_node WHERE groupid='$groupid' AND is_close='0'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0){			
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'下移转向规则失败，组内无服务器。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}												
		
		$rows_count = mysql_num_rows($result);
		for($i=0;$i<$rows_count;$i++){
			$node_id 		 = mysql_result($result,$i,"id");
			$node_ip 		 = mysql_result($result,$i,"ip");
			$node_password	 = mysql_result($result,$i,"password");
			$node_admin_port = mysql_result($result,$i,"admin_port");
			$node_auth_domain= mysql_result($result,$i,"auth_domain");
			$node_SessionID	 = mysql_result($result,$i,"SessionID");
			$groupid 		 = mysql_result($result,$i,"groupid");
			
			//添加删除任务，让后台去执行
			$timenow = time();
			$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
							VALUES(NULL,'$admin_username','$PubDefine_DownRewrite','$timenow',0,'$node_id',0,0,'',$groupid,'$old_SourceUrl','','')";
			$result2 = mysql_query($sql,$db_link);				
		}			
		
		$aryResult = array('Return'=>'True','groupid'=>$groupid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);			
	}
	else if($sAction=="sync")
	{
		$node_id = isset($_POST['node_id'])?$_POST['node_id']:'';
		$node_id2 = isset($_POST['node_id2'])?$_POST['node_id2']:'';
		
		//无添加服务器权限
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
			
		$db_link = FikCDNDB_Connect();
		if(!$db_link){
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'同步页面缓存失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name   		= mysql_result($result,0,"name");	
		$ip  		 	= mysql_result($result,0,"ip");
		$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
		$port   		= mysql_result($result,0,"port");	
		$admin_port   	= mysql_result($result,0,"admin_port");	
		$password   	= mysql_result($result,0,"password");
		$SessionID   	= mysql_result($result,0,"SessionID");	
		$auth_domain   	= mysql_result($result,0,"auth_domain");
		$groupid	   	= mysql_result($result,0,"groupid");
		$status	   		= mysql_result($result,0,"status");	
		
		$sql = "SELECT * FROM fikcdn_node WHERE id='$node_id2'";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoExist,'ErrorMsg'=>'同步缓存规则失败，服务器不存在。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}		
	
		$name2   		= mysql_result($result,0,"name");	
		$ip2  		 	= mysql_result($result,0,"ip");
		$unicom_ip2	 	= mysql_result($result,0,"unicom_ip");		
		$port2   		= mysql_result($result,0,"port");	
		$admin_port2   	= mysql_result($result,0,"admin_port");	
		$password2   	= mysql_result($result,0,"password");
		$SessionID2   	= mysql_result($result,0,"SessionID");	
		$auth_domain2   = mysql_result($result,0,"auth_domain");
		$groupid2	   	= mysql_result($result,0,"groupid");
		$status2	   	= mysql_result($result,0,"status");	
		
		//添加同步任务，让后台去执行
		$timenow = time();
		$sql = "INSERT INTO fikcdn_task(id,username,type,time,domain_id,node_id,product_id,buy_id,hostname,group_id,url,old_url,ext) 
						VALUES(NULL,'$admin_username','$PubDefine_SyncRewrite','$timenow',0,'$node_id',0,0,'',$groupid,'','','$node_id2')";
		$result2 = mysql_query($sql,$db_link);
		
		$aryResult = array('Return'=>'True','node_id'=>$node_id,'node_id2'=>$node_id2);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);							
	}			
}
?>
