<?php
set_time_limit(0);
include_once('../db/db.php');
include_once('../function/pub_function.php');
include_once('../function/define.php');
include_once('../function/fik_api.php');
include_once("function_admin.php");

$sMod 	 	 = isset($_GET['mod'])?$_GET['mod']:'';
$sAction 	 = isset($_GET['action'])?$_GET['action']:'';
$sCron  	 = isset($_GET['cron'])?$_GET['cron']:'';
if($sCron!="fik")
{
	exit();
}

//是否只允许本地IP运行 
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

if($sMod!='task')
{
	exit();
}

if($sAction!="execute")
{
	exit();
}

$db_link = FikCDNDB_Connect();
if(!$db_link)
{	
	$GLOBALS["ExecuteTaskEnter2"]=false;
	exit();
}

$sql ="SELECT * FROM fikcdn_params WHERE name='execute_task_enter3'";
$result = mysql_query($sql,$db_link);
if(!$result)
{
	mysql_close($db_link);
	exit();
}

if(mysql_num_rows($result)<=0)
{
	$timenow = time();
	$sql = "INSERT INTO fikcdn_params(id,name,value,time) VALUE(NULL,'execute_task_enter3','1','$timenow')";
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
	
	if($value == '1' && (time()-$time)<=600 )
	{
		mysql_close($db_link);
		exit();	
	}
		
	$sql = "UPDATE fikcdn_params SET value='1',time='$timenow' WHERE name='execute_task_enter3'";
	$result = mysql_query($sql,$db_link);	
	if(!$result)
	{
		mysql_close($db_link);
		exit();
	}	
}

$sql ="SELECT * FROM fikcdn_task WHERE type>=$PubDefine_AddFCache AND type<=$PubDefine_DelAllRewrite AND execute_count<$PubDefine_TaskMaxExecuteCount ORDER BY id ASC LIMIT 50";
$result = mysql_query($sql,$db_link);
if(!$result)
{
	$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='execute_task_enter3'";
	$result = mysql_query($sql,$db_link);	
	
	mysql_close($db_link);
	exit();
}

$row_count = mysql_num_rows($result);
for($i=0;$i<$row_count;$i++)
{		
	$task_id = mysql_result($result,$i,"id");
	$username = mysql_result($result,$i,"username");
	$type = mysql_result($result,$i,"type");
	$time	 = mysql_result($result,$i,"time");
	$domain_id	 = mysql_result($result,$i,"domain_id");
	$node_id	 = mysql_result($result,$i,"node_id");
	$product_id	 = mysql_result($result,$i,"product_id");
	$buy_id	 = mysql_result($result,$i,"buy_id");
	$hostname	 = mysql_result($result,$i,"hostname");
	$group_id	 = mysql_result($result,$i,"group_id");
	$ext	 = mysql_result($result,$i,"ext"); //json数据
	$url = mysql_result($result,$i,"url");
	$old_url = mysql_result($result,$i,"old_url");
	$execute_count= mysql_result($result,$i,"execute_count");
	
	echo "type=".$type.",task_id=".$task_id."<br />";
	
	//执行次数加1
	$sql = "UPDATE fikcdn_task SET execute_count = execute_count+1 WHERE id=$task_id";
	$result2 = mysql_query($sql,$db_link);
	
	if($type==$PubDefine_AddFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$aryFCache = json_decode($ext,true);
				
		$aryFCacheResult = FikApi_FCacheAdd($sFikIP,$this_admin_port,$this_SessionID,urldecode($aryFCache["Url"]),$aryFCache["Icase"],
								$aryFCache["Rules"],$aryFCache["Expire"],$aryFCache["Unit"],$aryFCache["Icookie"],
								$aryFCache["Olimit"],$aryFCache["IsDiskCache"],urldecode($aryFCache["Note"]));
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True"){
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryFCacheResult = FikApi_FCacheAdd($sFikIP,$this_admin_port,$this_SessionID,urldecode($aryFCache["Url"]),$aryFCache["Icase"],
										$aryFCache["Rules"],$aryFCache["Expire"],$aryFCache["Unit"],$aryFCache["Icookie"],
										$aryFCache["Olimit"],$aryFCache["IsDiskCache"],urldecode($aryFCache["Note"]));
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"] ==$FikCacheError_AddUrlExistOrSyntaxError){
				$sError = "Url 存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryFCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}
	else if($type==$PubDefine_ModifyFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$sUrl = urldecode($url);
		$sOldUrl = urldecode($old_url);
		$bNeedList = true;
		//echo 'old_url='.$sOldUrl.'<br />';
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //查询到原来的						
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
				
				//echo '<br />Url='.$Url.'  =========    sUrl='.$sUrl.'<br />';
				if($Url == $sOldUrl)
				{
					echo "find ok.".$sUrl;
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			echo "not exist.".$sUrl;
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			//$result2 = mysql_query($sql,$db_link);
			continue;
		}
				
		$aryFCache = json_decode($ext,true);
				
		$aryFCacheResult = FikApi_FCacheModify($sFikIP,$this_admin_port,$this_SessionID,$widModify,urldecode($aryFCache["Url"]),$aryFCache["Icase"],
								$aryFCache["Rules"],$aryFCache["Expire"],$aryFCache["Unit"],$aryFCache["Icookie"],
								$aryFCache["Olimit"],$aryFCache["IsDiskCache"],urldecode($aryFCache["Note"]));
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录节点会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}
			else if($aryFCacheResult["ErrorNo"] ==$FikCacheError_ModifyUrlError){
				$sError = "Url 不存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryFCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}	
	else if($type==$PubDefine_DelFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
						
		$aryFCacheResult = FikApi_FCacheDel($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryFCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_UpFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryFCacheResult = FikApi_FCacheUp($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryFCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_DownFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryFCacheResult = FikApi_FCacheDown($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryFCacheResult["Return"]=="False"){
			if($aryFCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryFCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_SyncFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$node_id2 = $ext;
		
		//获取节点2信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id2";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip2	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip2	= mysql_result($result2,0,"unicom_ip");
		$this_admin_port2	= mysql_result($result2,0,"admin_port");
		$this_username2	 	= mysql_result($result2,0,"username");
		$this_password2	 	= mysql_result($result2,0,"password");
		$this_SessionID2	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain2	= mysql_result($result2,0,"auth_domain");
		$this_domain_note2	= mysql_result($result2,0,"note");
		$this_is_transit2	= mysql_result($result2,0,"is_transit");
		$this_is_close2		= mysql_result($result2,0,"is_close");
		
		$sFikIP2 = $this_ip2;
		if(strlen($sFikIP2)<=0){
			$sFikIP2 = $node_unicom_ip2;
		}
		
		//同步
		$aryFikResult2 = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult2==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult2["Return"]=="False")
		{
			if($aryFikResult2["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult2 = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult2==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult2["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult2["Return"]=="True")
		{
		
		}		
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}		


		$aryFikResult = FikApi_FCacheList($sFikIP2,$this_admin_port2,$this_SessionID2);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id2,$sFikIP2,$this_admin_port2,$this_password2,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID2 = $aryRelogin["SessionID"];
					$aryFikResult =  FikApi_FCacheList($sFikIP2,$this_admin_port2,$this_SessionID2);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_FCacheDel($sFikIP2,$this_admin_port2,$this_SessionID2,$Wid);
			}
		}
		
		// 同步过去
		if($aryFikResult2["Return"]=="True")
		{
		    //先删除原来的						
			$nNumOfLists = $aryFikResult2["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult2["Lists"][$k]["NO"];
				$Wid = $aryFikResult2["Lists"][$k]["Wid"];
				$Url = $aryFikResult2["Lists"][$k]["Url"];
				$Icase = $aryFikResult2["Lists"][$k]["Icase"];
				$Rules = $aryFikResult2["Lists"][$k]["Rules"];
				$Expire = $aryFikResult2["Lists"][$k]["Expire"];
				$Unit = $aryFikResult2["Lists"][$k]["Unit"];
				$Icookie = $aryFikResult2["Lists"][$k]["Icookie"];
				$Olimit = $aryFikResult2["Lists"][$k]["Olimit"];
				$IsDiskCache = $aryFikResult2["Lists"][$k]["IsDiskCache"];
				$Note = $aryFikResult2["Lists"][$k]["Note"];
				
				//添加
				FikApi_FCacheAdd($sFikIP2,$this_admin_port2,$this_SessionID2,$Url,$Icase,$Rules,$Expire,$Unit,$Icookie,$Olimit,$IsDiskCache,$Note);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}			
	}
	else if($type==$PubDefine_DelAllFCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_FCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_FCacheDel($sFikIP,$this_admin_port,$this_SessionID,$Wid);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}	
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}	
	
	
	// ******************************************************************** //			
	// --------------------- 执行拒绝缓存的任务 ------------------------------ //
	// ******************************************************************** //
	if($type==$PubDefine_AddRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
				
		$aryRCache = json_decode($ext,true);
		print_r($aryRCache);
				
		$aryRCacheResult = FikApi_RCacheAdd($sFikIP,$this_admin_port,$this_SessionID,urldecode($aryRCache["Url"]),$aryRCache["Icase"],
								$aryRCache["Rules"],$aryRCache["Olimit"],$aryRCache["CacheLocation"],urldecode($aryRCache["Note"]));
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True"){
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryRCacheResult =FikApi_RCacheAdd($sFikIP,$this_admin_port,$this_SessionID,urldecode($aryRCache["Url"]),$aryRCache["Icase"],
										$aryRCache["Rules"],$aryRCache["Olimit"],$aryRCache["CacheLocation"],urldecode($aryRCache["Note"]));
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"] ==$FikCacheError_AddUrlExistOrSyntaxError){
				$sError = "Url 存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryRCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}
	else if($type==$PubDefine_ModifyRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$sUrl = urldecode($url);
		$sOldUrl = urldecode($old_url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sOldUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
				
		$aryRCache = json_decode($ext,true);
				
		$aryRCacheResult = FikApi_RCacheModify($sFikIP,$this_admin_port,$this_SessionID,$widModify,urldecode($aryRCache["Url"]),$aryRCache["Icase"],
								$aryRCache["Rules"],$aryRCache["Olimit"],$aryRCache["CacheLocation"],urldecode($aryRCache["Note"]));
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}
			else if($aryRCacheResult["ErrorNo"] ==$FikCacheError_ModifyUrlError){
				$sError = "Url 不存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryRCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}	
	else if($type==$PubDefine_DelRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRCacheResult = FikApi_RCacheDel($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_UpRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRCacheResult = FikApi_RCacheUp($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_DownRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($Url == $sUrl)
				{
					$widModify  = $Wid;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($widModify==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRCacheResult = FikApi_RCacheDown($sFikIP,$this_admin_port,$this_SessionID,$widModify);
		if($aryRCacheResult["Return"]=="False"){
			if($aryRCacheResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRCacheResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRCacheResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_SyncRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$node_id2 = $ext;
		
		//获取节点2信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id2";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip2	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip2		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port2	= mysql_result($result2,0,"admin_port");
		$this_username2	 	= mysql_result($result2,0,"username");
		$this_password2	 	= mysql_result($result2,0,"password");
		$this_SessionID2	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain2	= mysql_result($result2,0,"auth_domain");
		$this_domain_note2	= mysql_result($result2,0,"note");
		$this_is_transit2	= mysql_result($result2,0,"is_transit");
		$this_is_close2		= mysql_result($result2,0,"is_close");
		
		$sFikIP2 = $this_ip2;
		if(strlen($sFikIP2)<=0){
			$sFikIP2 = $node_unicom_ip2;
		}
		
		//同步
		$aryFikResult2 = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult2==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult2["Return"]=="False")
		{
			if($aryFikResult2["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult2 = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult2==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult2["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult2["Return"]=="True")
		{
		
		}		
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
				
		
		$aryFikResult = FikApi_RCacheList($sFikIP2,$this_admin_port2,$this_SessionID2);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id2,$sFikIP2,$this_admin_port2,$this_password2,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID2 = $aryRelogin["SessionID"];
					$aryFikResult =  FikApi_RCacheList($sFikIP2,$this_admin_port2,$this_SessionID2);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_RCacheDel($sFikIP2,$this_admin_port2,$this_SessionID2,$Wid);
			}
		}

		// 同步过去
		if($aryFikResult2["Return"]=="True")
		{
		    //先删除原来的						
			$nNumOfLists = $aryFikResult2["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult2["Lists"][$k]["NO"];
				$Wid = $aryFikResult2["Lists"][$k]["Wid"];
				$Url = $aryFikResult2["Lists"][$k]["Url"];
				$Icase = $aryFikResult2["Lists"][$k]["Icase"];
				$Rules = $aryFikResult2["Lists"][$k]["Rules"];
				$Olimit = $aryFikResult2["Lists"][$k]["Olimit"];
				$CacheLocation = $aryFikResult2["Lists"][$k]["CacheLocation"];
				$Note = $aryFikResult2["Lists"][$k]["Note"];
				
				//添加
				FikApi_RCacheAdd($sFikIP2,$this_admin_port2,$this_SessionID2,$Url,$Icase,$Rules,$Olimit,$CacheLocation,$Note);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}	
	}
	else if($type==$PubDefine_DelAllRCache)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RCacheList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_RCacheDel($sFikIP,$this_admin_port,$this_SessionID,$Wid);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}	
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}	
		
	// ******************************************************************** //			
	// --------------------- 执行转向规则的任务 ------------------------------ //
	// ******************************************************************** //
	if($type==$PubDefine_AddRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$aryRewrite = json_decode($ext,true);
				
		$aryRewriteResult = FikApi_RewriteAdd($sFikIP,$this_admin_port,$this_SessionID,urldecode($aryRewrite["SourceUrl"]),urldecode($aryRewrite["DestinationUrl"]),
								$aryRewrite["Icase"],$aryRewrite["Flag"],urldecode($aryRewrite["Note"]));
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True"){
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryRewriteResult = FikApi_RewriteAdd($sFikIP,$this_admin_port,$this_SessionID,$aryRewrite["SourceUrl"],$aryRewrite["DestinationUrl"],
											$aryRewrite["Icase"],$aryRewrite["Flag"],urldecode($aryRewrite["Note"]));
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"] ==$FikCacheError_AddRewriteUrlError){
				$sError = "Url 存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryRewriteResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRewriteResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}
	else if($type==$PubDefine_ModifyRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$sOldUrl = urldecode($old_url);
		$bNeedList = true;
		echo $sOldUrl.'<br />';
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$thisRewriteID = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				echo "SourceUrl=".$SourceUrl,',sOldUrl='.$sOldUrl.'<br />';
				if($SourceUrl == $sOldUrl)
				{
					$thisRewriteID  = $RewriteID;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($thisRewriteID==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
				
		$aryRewrite = json_decode($ext,true);
				
		$aryRewriteResult = FikApi_RewriteModify($sFikIP,$this_admin_port,$this_SessionID,$thisRewriteID,urldecode($aryRewrite["SourceUrl"]),urldecode($aryRewrite["DestinationUrl"]),
								$aryRewrite["Icase"],$aryRewrite["Flag"],urldecode($aryRewrite["Note"]));
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}
			else if($aryRewriteResult["ErrorNo"] ==$FikCacheError_ModifyRewriteUrlError){
				$sError = "Url 不存在或者语法错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			$sError = "错误号：".$aryRewriteResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRewriteResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}
	}	
	else if($type==$PubDefine_DelRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$thisRewriteID = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($SourceUrl == $sUrl)
				{
					$thisRewriteID  = $RewriteID;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($thisRewriteID==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRewriteResult = FikApi_RewriteDel($sFikIP,$this_admin_port,$this_SessionID,$thisRewriteID);
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRewriteResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRewriteResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_UpRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$thisRewriteID = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($SourceUrl == $sUrl)
				{
					$thisRewriteID  = $RewriteID;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($thisRewriteID==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRewriteResult = FikApi_RewriteUp($sFikIP,$this_admin_port,$this_SessionID,$thisRewriteID);
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRewriteResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRewriteResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_DownRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");

		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$sUrl = urldecode($url);
		$bNeedList = true;
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$thisRewriteID = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				if($SourceUrl == $sUrl)
				{
					$thisRewriteID  = $RewriteID;
					break;
				}
			}
		}	
		
		//修改错误，规则不存在
		if($thisRewriteID==-1){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			continue;
		}
								
		$aryRewriteResult = FikApi_RewriteDown($sFikIP,$this_admin_port,$this_SessionID,$thisRewriteID);
		if($aryRewriteResult["Return"]=="False"){
			if($aryRewriteResult["ErrorNo"]==$FikCacheError_SessionHasOverdue){
				$sError = "登录会话超时";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}

			$sError = "错误号：".$aryRewriteResult["ErrorNo"];
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryRewriteResult["Return"]=="True"){
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}else{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_SyncRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		if(strlen($ext)<0){
			// 任务参数不对
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		$node_id2 = $ext;
		
		//获取节点2信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id2";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip2	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip2		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port2	= mysql_result($result2,0,"admin_port");
		$this_username2	 	= mysql_result($result2,0,"username");
		$this_password2	 	= mysql_result($result2,0,"password");
		$this_SessionID2	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain2	= mysql_result($result2,0,"auth_domain");
		$this_domain_note2	= mysql_result($result2,0,"note");
		$this_is_transit2	= mysql_result($result2,0,"is_transit");
		$this_is_close2		= mysql_result($result2,0,"is_close");
		
		$sFikIP2 = $this_ip2;
		if(strlen($sFikIP2)<=0){
			$sFikIP2 = $node_unicom_ip2;
		}
		
	//同步
		$aryFikResult2 = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult2==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult2["Return"]=="False")
		{
			if($aryFikResult2["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult2 = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult2==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult2["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult2["Return"]=="True")
		{
		
		}
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
				
		$aryFikResult = FikApi_RewriteList($sFikIP2,$this_admin_port2,$this_SessionID2);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id2,$sFikIP2,$this_admin_port2,$this_password2,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID2 = $aryRelogin["SessionID"];
					$aryFikResult =  FikApi_RewriteList($sFikIP2,$this_admin_port2,$this_SessionID2);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		$widModify = -1;
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_RewriteDel($sFikIP2,$this_admin_port2,$this_SessionID2,$RewriteID);
			}
		}	

		// 同步
		if($aryFikResult2["Return"]=="True")
		{
		    //先删除原来的						
			$nNumOfLists = $aryFikResult2["NumOfLists"];
			for($k=0;$k<$nNumOfLists;$k++)
			{
				$NO = $aryFikResult2["Lists"][$k]["NO"];
				$RewriteID = $aryFikResult2["Lists"][$k]["RewriteID"];
				$SourceUrl = $aryFikResult2["Lists"][$k]["SourceUrl"];
				$DestinationUrl = $aryFikResult2["Lists"][$k]["DestinationUrl"];
				$Icase = $aryFikResult2["Lists"][$k]["Icase"];
				$Flag = $aryFikResult2["Lists"][$k]["Flag"];
				$Note = $aryFikResult2["Lists"][$k]["Note"];
				
				//添加
				FikApi_RewriteAdd($sFikIP2,$this_admin_port2,$this_SessionID2,$SourceUrl,$DestinationUrl,$Icase,$Flag,$Note);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}	
		
		continue;
	}
	else if($type==$PubDefine_DelAllRewrite)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			// 无此服务器，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}			
		
		$this_ip	 		= mysql_result($result2,0,"ip");
		$node_unicom_ip		= mysql_result($result2,0,"unicom_ip");
		$this_admin_port	= mysql_result($result2,0,"admin_port");
		$this_username	 	= mysql_result($result2,0,"username");
		$this_password	 	= mysql_result($result2,0,"password");
		$this_SessionID	 	= mysql_result($result2,0,"SessionID");
		$this_auth_domain	= mysql_result($result2,0,"auth_domain");
		$this_domain_note	= mysql_result($result2,0,"note");
		$this_is_transit	= mysql_result($result2,0,"is_transit");
		$this_is_close		= mysql_result($result2,0,"is_close");
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
		if($aryFikResult==false){
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
		
		if($aryFikResult["Return"]=="False")
		{
			if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					$aryFikResult = FikApi_RewriteList($sFikIP,$this_admin_port,$this_SessionID);
					if($aryFikResult==false){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}	
				}else{
					if($aryRelogin["ErrorNo"]==-1){
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}else if($aryRelogin["ErrorNo"]==-2){
						if($aryRelogin[$FikErrorNo]==$FikCacheError_PasswordError){
							$sError = "节点管理员密码错误";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}
					
					$sError = "登录 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
			}
		}
		
		if($aryFikResult["Return"]=="False")
		{
			$sError = "Fikker 节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		else if($aryFikResult["Return"]=="True")
		{
		    //先删除原来的						
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
				
				//删除
				FikApi_RewriteDel($sFikIP,$this_admin_port,$this_SessionID,$RewriteID);
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
				
			continue;
		}	
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}		
}

$timenow = time();
$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='execute_task_enter3'";
$result = mysql_query($sql,$db_link);	
if(!$result)
{
	mysql_close($db_link);
	exit();
};

mysql_close($db_link);

?>
