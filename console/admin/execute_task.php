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
	$GLOBALS["ExecuteTaskEnter"]=false;
	exit();
}


$sql ="SELECT * FROM fikcdn_params WHERE name='execute_task_enter'";
$result = mysql_query($sql,$db_link);
if(!$result)
{
	mysql_close($db_link);
	exit();
}

if(mysql_num_rows($result)<=0)
{
	$timenow = time();
	$sql = "INSERT INTO fikcdn_params(id,name,value,time) VALUE(NULL,'execute_task_enter','1','$timenow')";
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

	$sql = "UPDATE fikcdn_params SET value='1',time='$timenow' WHERE name='execute_task_enter'";
	$result = mysql_query($sql,$db_link);	
	if(!$result)
	{
		mysql_close($db_link);
		exit();
	}	
}

$sql ="SELECT * FROM fikcdn_task WHERE type<=$PubDefine_TaskAddProxy AND execute_count<$PubDefine_TaskMaxExecuteCount ORDER BY id ASC LIMIT 100";
$result = mysql_query($sql,$db_link);
if(!$result)
{
	$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='execute_task_enter'";
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
	$ext	 = mysql_result($result,$i,"ext"); //清理缓存时，当成 url
	$execute_count= mysql_result($result,$i,"execute_count");
	
	echo "type=".$type.",task_id=".$task_id."<br />";
	
	//执行次数加1
	$sql = "UPDATE fikcdn_task SET execute_count = execute_count+1 WHERE id=$task_id";
	$result2 = mysql_query($sql,$db_link);
	
	if($type==$PubDefine_TaskModifyUpstream)
	{
		//获取域名信息
		$sql = "SELECT * FROM fikcdn_domain WHERE id=$domain_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{
				$sError = "查询域名错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

			// 域名不存在，直接删除任务			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}
	
		$this_domain_id	 = mysql_result($result2,0,"id");
		$this_hostname	 = mysql_result($result2,0,"hostname");
		$this_upstream	 = mysql_result($result2,0,"upstream");
		$this_unicom_ip	 = mysql_result($result2,0,"unicom_ip");
		$use_transit_node= mysql_result($result2,0,"use_transit_node");
		$this_group_id	 = mysql_result($result2,0,"group_id");
		$this_buy_id	 = mysql_result($result2,0,"buy_id");		
		$this_status	 = mysql_result($result2,0,"status");
		$upstream_add_all= mysql_result($result2,0,"upstream_add_all");
		$SSLOpt	 		 = mysql_result($result2,0,"SSLOpt");
		$SSLCrtContent	 = mysql_result($result2,0,"SSLCrtContent");
		$SSLKeyContent	 = mysql_result($result2,0,"SSLKeyContent");
		$SSLExtraParams	 = mysql_result($result2,0,"SSLExtraParams");
		$UpsSSLOpt		 = mysql_result($result2,0,"UpsSSLOpt");
					
		//域名还在审核
		if($this_status==$PubDefine_HostStatusVerify)
		{			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);

			continue;
		}	
				
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
		$fik_version		= mysql_result($result2,0,"fik_version");
		
		if($this_is_close)
		{			
			// 服务器已经停用，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}
				
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0){
			$sFikIP = $node_unicom_ip;
		}
		
		$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
		if($show_version>="3.6.2")
		{
			$isNeedModify = false;
			$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
			print_r($aryProxyAdd);
			if($aryProxyAdd["Return"]=="False")
			{
				if($aryProxyAdd["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
						if($aryProxyAdd["Return"]=="False")
						{
							if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
							{
								$nProxyID = $aryProxyAdd["ProxyID"];
								$isNeedModify = true;
							}
						}
					}					
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
				{
					$nProxyID = $aryProxyAdd["ProxyID"];
					$isNeedModify = true;
				}
			}
			
			if($aryProxyAdd["Return"]=="True")
			{
				$nProxyID = $aryProxyAdd["ProxyID"];
			}
			else if($aryProxyAdd["Return"]=="False")
			{
				if(!$isNeedModify)
				{
					$nErrorNo = $aryProxyAdd["ErrorNo"];
					if($nErrorNo == $FikCacheError_SSLCrtError)
					{
						$sError = "Fikker 返回错误，数字证书错误";
					}
					else if($nErrorNo == $FikCacheError_SSLKeyError)
					{
						$sError = "Fikker 返回错误，私钥错误";
					}
					else if($nErrorNo == $FikCacheError_CrtAndKeyNotMatch)
					{
						$sError = "Fikker 返回错误，数字证书和私钥不匹配";
					}
					else
					{
						$sError = "Fikker 返回错误，错误号：".$nErrorNo;
					}
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					continue;
				}
			}	
			else
			{
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				continue;
			}	
						
			if($nProxyID>=0)
			{
				if($isNeedModify)
				{
					$aryProxyModify = FikApi_SSLProxyModify($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
					print_r($aryProxyModify);
						
					//获取源站列表
					$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
					if($aryUpstreamList["Return"]=="True")
					{
						$nNumOfLists = $aryUpstreamList["NumOfLists"];
						for($k=0;$k<$nNumOfLists;$k++)
						{
							$nNo = $aryUpstreamList["Lists"][$k]["NO"];
							$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
							
							//删除源站IP
							$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
							if($aryUpstreamDel["Return"]!="True")
							{
								continue;
							}
						}
					}					
				}
				
				//添加源站
				$sAddUpstream ="";
				$sAddUpstream2="";
				
				$this_upstream_ip="";
			
				$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
				$result5 = mysql_query($sql,$db_link);
				if($result5 && mysql_num_rows($result5)>0)
				{
					$sUpstream   = mysql_result($result5,0,"upstream");
					$sUpstream2  = mysql_result($result5,0,"upstream2");
					$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
					
					if($upstream_add_all2==0)
					{
						if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else if(strlen($this_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}					
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else
						{
							if(strlen($sUpstream2)>0)
							{
								$sAddUpstream = $sUpstream2;
							}					
							else
							{
								$sAddUpstream = $sUpstream;
							}
						}
					}
					else
					{
						$sAddUpstream = $sUpstream;
						$sAddUpstream2 =$sUpstream2;
					}
					
					if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
					{							
						$sError = "无源站IP";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
					
					if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
					{
						$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
							
						continue;
					}
							
					if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
					{							
						$sError = "添加源站失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}						
				}
			
				if(!$result5)
				{
					$sError = "查询源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
			
				if($upstream_add_all==0)
				{
					if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else if(strlen($this_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}					
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else
					{
						if(strlen($this_unicom_ip)>0)
						{
							$sAddUpstream = $this_unicom_ip;
						}					
						else
						{
							$sAddUpstream = $this_upstream;
						}
					}	
				}
				else
				{
					$sAddUpstream  = $this_upstream;
					$sAddUpstream2 = $this_unicom_ip;
				}
				
				if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
				{							
					$sError = "无源站IP";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}	
										
				if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
				{				
					$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
												
				$sError = "添加源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			$sError = "添加域名失败，节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);			
				
			continue;
		}		
		
		// 以下为老版本的同步		
		$bQueryHostOk = false;
		
		//从 domain_stat_temp 表中查询域名
		$sql = "SELECT * FROM domain_stat_temp WHERE node_id=$node_id AND Host='$this_hostname'";
		$result3 = mysql_query($sql,$db_link);
		if($result3 && mysql_num_rows($result3)>0)
		{
			$nProxyID	= mysql_result($result3,0,"ProxyID");		
						
			$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryProxyQuery["Return"]=="True")
			{
				$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
				if($sQueryHost==$this_hostname)
				{
					echo '1. sQueryHost==this_hostname,==$sQueryHost<br />';
					$bQueryHostOk=true;
				}
				else
				{
					echo '1. sQueryHost!=this_hostname,==$sQueryHost<br />';
					var_dump($aryProxyQuery);
				}
			}
			else if($aryProxyQuery["Return"]=="False")
			{
				if($aryProxyQuery["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						//重新获取域名列表
						$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);			
						if($aryProxyQuery["Return"]=="True")
						{
							$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
							if($sQueryHost==$this_hostname)
							{
								echo '2. sQueryHost==this_hostname,==$sQueryHost<br />';
								$bQueryHostOk=true;
							}
							else
							{
								echo '2. sQueryHost!=this_hostname,==$sQueryHost<br />';
								var_dump($aryProxyQuery);
							}
						}
					}
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else
				{
					/*
					$sError = "Fikker 返回错误，错误号：".$aryProxyAdd["ErrorNo"];
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
					*/
				}
			}		
			else
			{
			    /*
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
				*/
			}				
		}	
		
		$IsAddProxy = false;
		
		if($bQueryHostOk)
		{
			$IsAddProxy = true;
			
			//获取源站列表
			$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryUpstreamList["Return"]=="True")
			{
				$nNumOfLists = $aryUpstreamList["NumOfLists"];
				for($k=0;$k<$nNumOfLists;$k++)
				{
					$nNo = $aryUpstreamList["Lists"][$k]["NO"];
					$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
					
					//删除源站IP
					$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
					if($aryUpstreamDel["Return"]!="True")
					{
						$sError = "删除原来的源站失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					
						continue;
					}
				}		
				
				$IsAddProxy=true;	
			}
			else
			{
				$sError = "获取源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
		}
		else
		{
			$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
			if($aryProxyQuery["Return"]=="False")
			{
				if($aryProxyQuery["ErrorNo"]==-2)
				{
					if($aryProxyQuery["FikErrorNo"]==$FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$this_SessionID = $aryRelogin["SessionID"];
							
							//重新获取域名列表
							$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
						}
						else
						{
							if($aryRelogin["ErrorNo"]==-1)
							{
								$sError = "连接 Fikker 节点失败";
								$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
								$result2 = mysql_query($sql,$db_link);
							}
							else
							{
								$sError = "";
								$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
								$result2 = mysql_query($sql,$db_link);
							}
								
							continue;
						}
					}
				}
				else if($aryProxyQuery["ErrorNo"]==-1)
				{
					$sError = "连接 Fikker 节点失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
			}
		
			$IsAddProxy = false;
		
			$nProxyID=-100;
				
			if($aryProxyQuery["Return"]=="False")
			{
				//没有这个域名就增加
				if($aryProxyQuery["ErrorNo"]==-3)
				{
					$aryProxyAdd = FikApi_ProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_domain_note);
					if($aryProxyAdd["Return"]=="True")
					{
						$nProxyID = $aryProxyAdd["ProxyID"];
						$IsAddProxy=true;
					}
					else if($aryProxyAdd["Return"]=="False")
					{
						$sError = "Fikker 返回错误，错误号：".$aryProxyAdd["ErrorNo"];
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}		
					else
					{
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}				
				}
			}
			else if($aryProxyQuery["Return"]=="True")
			{
				$nProxyID = $aryProxyQuery["ProxyID"];
				
				//获取源站列表
				$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
				if($aryUpstreamList["Return"]=="True")
				{
					$nNumOfLists = $aryUpstreamList["NumOfLists"];
					for($k=0;$k<$nNumOfLists;$k++)
					{
						$nNo = $aryUpstreamList["Lists"][$k]["NO"];
						$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
						
						//删除源站IP
						$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
						if($aryUpstreamDel["Return"]!="True")
						{
							$sError = "删除原来的源站失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						
							continue;
						}
					}		
					
					$IsAddProxy=true;	
				}
				else
				{
					$sError = "获取源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
			}
		}
			
		$sAddUpstream="";
		$sAddUpstream2="";
			
		if($IsAddProxy && $nProxyID>=0)
		{
			$this_upstream_ip="";
			$sAddUpstream2 = "";
			
			$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
			$result5 = mysql_query($sql,$db_link);
			if($result5 && mysql_num_rows($result5)>0)
			{
				$sUpstream   = mysql_result($result5,0,"upstream");
				$sUpstream2  = mysql_result($result5,0,"upstream2");
				$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
				
				if($upstream_add_all2==0)
				{
					if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
					{
						if(strlen($sUpstream)>0)
						{
							$sAddUpstream = $sUpstream;
						}
						else
						{
							$sAddUpstream = $sUpstream2;
						}
					}
					else if(strlen($this_ip)>0)
					{
						if(strlen($sUpstream)>0)
						{
							$sAddUpstream = $sUpstream;
						}					
						else
						{
							$sAddUpstream = $sUpstream2;
						}
					}
					else
					{
						if(strlen($sUpstream2)>0)
						{
							$sAddUpstream = $sUpstream2;
						}					
						else
						{
							$sAddUpstream2 = $sUpstream2;
						}
					}
				}
				else
				{	
					$sAddUpstream = $sUpstream;
					$sAddUpstream2 = $sUpstream;
				}
				
				if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
				{							
					$sError = "无源站IP";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
				
				if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2))
				{
					$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
						
				if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
				{							
					$sError = "添加源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}	
			}	
			
			if(!$result5)
			{
				$sError = "查询源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			if($upstream_add_all==0)
			{
				if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
				{
					if(strlen($this_upstream)>0)
					{
						$sAddUpstream = $this_upstream;
					}
					else
					{
						$sAddUpstream = $this_unicom_ip;
					}
				}
				else if(strlen($this_ip)>0)
				{
					if(strlen($this_upstream)>0)
					{
						$sAddUpstream = $this_upstream;
					}					
					else
					{
						$sAddUpstream = $this_unicom_ip;
					}
				}
				else
				{
					if(strlen($this_unicom_ip)>0)
					{
						$sAddUpstream = $this_unicom_ip;
					}					
					else
					{
						$sAddUpstream = $this_upstream;
					}
				}			
			}
			else
			{	
				$sAddUpstream = $this_upstream;
				$sAddUpstream2 = $this_unicom_ip;
			}
									
			if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
			{							
				$sError = "无源站IP";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
										
			if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2))
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}
											
			$sError = "添加源站失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;		
		}
	}
	else if($type==$PubDefine_TaskDelDomain)
	{
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{			
			if(!$result2)
			{
				$sError = "获取服务器失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
	
				continue;
			}

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
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;
		
		$nProxyID = -100;
		$bQueryHostOk = false;
		
		//从 domain_stat_temp 表中查询域名
		$sql = "SELECT * FROM domain_stat_temp WHERE node_id=$node_id AND Host='$this_hostname'";
		$result3 = mysql_query($sql,$db_link);
		if($result3 && mysql_num_rows($result3)>0)
		{
			$nProxyID	= mysql_result($result3,0,"ProxyID");		
			
			$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryProxyQuery["Return"]=="True")
			{
				$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
				if($sQueryHost==$this_hostname)
				{
					$bQueryHostOk=true;
				}
			}
			else if($aryProxyQuery["Return"]=="False")
			{
				if($aryProxyQuery["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						//重新获取域名列表
						$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);			
						if($aryProxyQuery["Return"]=="True")
						{
							$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
							if($sQueryHost==$this_hostname)
							{
								$bQueryHostOk=true;
							}
						}
					}
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败1";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else
				{
					/*
					$sError = "Fikker 返回错误，错误号：".$aryProxyAdd["ErrorNo"];
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
					*/
				}
			}		
			else
			{
				/*
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
				*/
			}				
		}			
		
		if($bQueryHostOk && $nProxyID>=0)
		{
			echo "find 0k----------------<br />";
			
			//删除域名
			$aryProxyDel = FikApi_ProxyDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryProxyDel["Return"]=="True")
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;	
			}				
			else
			{
				$sError = "删除域名失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
		}				
		
		$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$hostname);
		if($aryProxyQuery["Return"]=="False")
		{
			if($aryProxyQuery["ErrorNo"]==-2)
			{
				if($aryProxyQuery["FikErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						//重新获取域名列表
						$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
					}					
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败2";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
			}
			else if($aryProxyQuery["ErrorNo"]==-3)
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;	
			}			
			else if($aryProxyQuery["ErrorNo"]==-1)
			{
				$sError = "连接 Fikker 节点失败3";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
		}
					
		if($aryProxyQuery["Return"]=="True")
		{
			$nProxyID = $aryProxyQuery["ProxyID"];
			
			//删除域名
			$aryProxyDel = FikApi_ProxyDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryProxyDel["Return"]=="True")
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;	
			}				
			else
			{
				$sError = "删除域名失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
		}
	}
	else if($type==$PubDefine_TaskModifyDomainStatus)
	{
		//获取域名信息
		$sql = "SELECT * FROM fikcdn_domain WHERE id=$domain_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{				
				$sError = "查询域名错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);		
			
			continue;			
		}
	
		$this_domain_id	 = mysql_result($result2,0,"id");
		$this_hostname	 = mysql_result($result2,0,"hostname");
		$this_upstream	 = mysql_result($result2,0,"upstream");
		$this_unicom_ip	 = mysql_result($result2,0,"unicom_ip");
		$this_group_id	 = mysql_result($result2,0,"group_id");
		$this_buy_id	 = mysql_result($result2,0,"buy_id");
		$this_status	 = mysql_result($result2,0,"status");
		$use_transit_node= mysql_result($result2,0,"use_transit_node");
		$upstream_add_all= mysql_result($result2,0,"upstream_add_all");
		
		$SSLOpt			= mysql_result($result2,0,"SSLOpt");
		$SSLKeyContent	= mysql_result($result2,0,"SSLKeyContent");	
		$SSLCrtContent	= mysql_result($result2,0,"SSLCrtContent");	
		$SSLExtraParams	= mysql_result($result2,0,"SSLExtraParams");	
		$UpsSSLOpt		= mysql_result($result2,0,"UpsSSLOpt");			
		
		//域名还在审核
		if($this_status==$PubDefine_HostStatusVerify)
		{			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);

			continue;
		}			
		
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
		$this_is_close		= mysql_result($result2,0,"is_close");
		$fik_version		= mysql_result($result2,0,"fik_version");
		
		if($this_is_close)
		{			
			// 服务器已经停用，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}
				
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;		
		
		$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
		if($show_version>="3.6.2")
		{
			$isNeedModify = false;
			$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
			print_r($aryProxyAdd);
			if($aryProxyAdd["Return"]=="False")
			{
				if($aryProxyAdd["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
						if($aryProxyAdd["Return"]=="False")
						{
							if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
							{
								$nProxyID = $aryProxyAdd["ProxyID"];
								$isNeedModify = true;
							}
						}
					}					
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
				{
					$nProxyID = $aryProxyAdd["ProxyID"];
					$isNeedModify = true;
				}
			}
			
			if($aryProxyAdd["Return"]=="True")
			{
				$nProxyID = $aryProxyAdd["ProxyID"];
			}
			else if($aryProxyAdd["Return"]=="False")
			{
				if(!$isNeedModify)
				{
					$nErrorNo = $aryProxyAdd["ErrorNo"];
					if($nErrorNo == $FikCacheError_SSLCrtError)
					{
						$sError = "Fikker 返回错误，数字证书错误";
					}
					else if($nErrorNo == $FikCacheError_SSLKeyError)
					{
						$sError = "Fikker 返回错误，私钥错误";
					}
					else if($nErrorNo == $FikCacheError_CrtAndKeyNotMatch)
					{
						$sError = "Fikker 返回错误，数字证书和私钥不匹配";
					}
					else
					{
						$sError = "Fikker 返回错误，错误号：".$nErrorNo;
					}
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					continue;
				}
			}	
			else
			{
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				continue;
			}
						
			if($nProxyID>=0)
			{
				if($isNeedModify)
				{
					$aryProxyEnable = FikApi_ProxyEnable($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$this_status);
					if($aryProxyEnable["Return"]=="True")
					{
						$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
					else
					{
						$sError = "设置域名状态错误";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						continue;
					}				
				}
				
				//添加源站
				$sAddUpstream ="";
				$sAddUpstream2="";
				
				$this_upstream_ip="";
			
				$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
				$result5 = mysql_query($sql,$db_link);
				if($result5 && mysql_num_rows($result5)>0)
				{
					$sUpstream   = mysql_result($result5,0,"upstream");
					$sUpstream2  = mysql_result($result5,0,"upstream2");
					$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
					
					if($upstream_add_all2==0)
					{
						if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else if(strlen($this_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}					
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else
						{
							if(strlen($sUpstream2)>0)
							{
								$sAddUpstream = $sUpstream2;
							}					
							else
							{
								$sAddUpstream = $sUpstream;
							}
						}
					}
					else
					{
						$sAddUpstream = $sUpstream;
						$sAddUpstream2 =$sUpstream2;
					}
					
					if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
					{							
						$sError = "无源站IP";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
					
					if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
					{
						$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
							
						continue;
					}
							
					if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
					{							
						$sError = "添加源站失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}						
				}
			
				if(!$result5)
				{
					$sError = "查询源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
			
				if($upstream_add_all==0)
				{
					if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else if(strlen($this_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}					
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else
					{
						if(strlen($this_unicom_ip)>0)
						{
							$sAddUpstream = $this_unicom_ip;
						}					
						else
						{
							$sAddUpstream = $this_upstream;
						}
					}	
				}
				else
				{
					$sAddUpstream  = $this_upstream;
					$sAddUpstream2 = $this_unicom_ip;
				}
				
				if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
				{							
					$sError = "无源站IP";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}	
										
				if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
				{				
					$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
												
				$sError = "添加源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			$sError = "添加域名失败，节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);			
				
			continue;
		}
				
		$nProxyID = -100;
		$bQueryHostOk = false;
		
		//从 domain_stat_temp 表中查询域名
		$sql = "SELECT * FROM domain_stat_temp WHERE node_id=$node_id AND Host='$this_hostname'";
		$result3 = mysql_query($sql,$db_link);
		if($result3 && mysql_num_rows($result3)>0)
		{
			$nProxyID	= mysql_result($result3,0,"ProxyID");		
			
			$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
			if($aryProxyQuery["Return"]=="True")
			{
				$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
				if($sQueryHost==$this_hostname)
				{
					$bQueryHostOk=true;
				}
			}
			else if($aryProxyQuery["Return"]=="False")
			{
				if($aryProxyQuery["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						//重新获取域名列表
						$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);			
						if($aryProxyQuery["Return"]=="True")
						{
							$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
							if($sQueryHost==$this_hostname)
							{
								$bQueryHostOk=true;
							}
						}
					}
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else
				{
					/*
					$sError = "Fikker 返回错误，错误号：".$aryProxyAdd["ErrorNo"];
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
					*/
				}
			}		
			else
			{
				/*
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
				*/
			}				
		}			
		
		if($bQueryHostOk && $nProxyID>=0)
		{
			echo "find 0k";
			
			$aryProxyEnable = FikApi_ProxyEnable($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$this_status);
			if($aryProxyEnable["Return"]=="True")
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			else
			{
				$sError = "设置域名状态错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				continue;
			}
		
		}		
				
		$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
		if($aryProxyQuery["Return"]=="False")
		{
			if($aryProxyQuery["ErrorNo"]==-2)
			{
				if($aryProxyQuery["FikErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						//重新获取域名列表
						$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
					}
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
			}			
			else if($aryProxyQuery["ErrorNo"]==-1)
			{
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
		}
		
		$sAddUpstream = "";
		$sAddUpstream2="";
		
		if($aryProxyQuery["Return"]=="False")
		{
			//没有这个域名就增加
			if($aryProxyQuery["ErrorNo"]==-3)
			{
				$aryProxyAdd = FikApi_ProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_domain_note);
				if($aryProxyAdd["Return"]=="True")
				{
					$nProxyID = $aryProxyAdd["ProxyID"];
					
					if($this_status==$PubDefine_HostStatusStop)
					{
						$aryProxyEnable = FikApi_ProxyEnable($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,0);
						if($aryProxyEnable["Return"]!="True")
						{							
							continue;
						}							
					}						
					
					$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
					$result5 = mysql_query($sql,$db_link);
					if($result5 && mysql_num_rows($result5)>0)
					{
						$sUpstream   = mysql_result($result5,0,"upstream");
						$sUpstream2  = mysql_result($result5,0,"upstream2");
						$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
						
						if($upstream_add_all2==0)
						{
							if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
							{
								if(strlen($sUpstream)>0)
								{
									$sAddUpstream = $sUpstream;
								}
								else
								{
									$sAddUpstream = $sUpstream2;
								}
							}
							else if(strlen($this_ip)>0)
							{
								if(strlen($sUpstream)>0)
								{
									$sAddUpstream = $sUpstream;
								}					
								else
								{
									$sAddUpstream = $sUpstream2;
								}
							}
							else
							{
								if(strlen($sUpstream2)>0)
								{
									$sAddUpstream = $sUpstream2;
								}					
								else
								{
									$sAddUpstream = $sUpstream;
								}
							}
						}
						else
						{
							$sAddUpstream = $sUpstream;
							$sAddUpstream2 = $sUpstream2;
						}
						
						if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
						{							
							$sError = "无源站IP";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
						
						if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2))
						{
							$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
								
							continue;
						}
								
						if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
						{							
							$sError = "添加源站失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}
					}	
					
					if(!$result5)
					{
						$sError = "查询源站失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
								
					if($upstream_add_all==0)
					{
						if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
						{
							if(strlen($this_upstream)>0)
							{
								$sAddUpstream = $this_upstream;
							}
							else
							{
								$sAddUpstream = $this_unicom_ip;
							}
						}
						else if(strlen($this_ip)>0)
						{
							if(strlen($this_upstream)>0)
							{
								$sAddUpstream = $this_upstream;
							}					
							else
							{
								$sAddUpstream = $this_unicom_ip;
							}
						}
						else
						{
							if(strlen($this_unicom_ip)>0)
							{
								$sAddUpstream = $this_unicom_ip;
							}					
							else
							{
								$sAddUpstream = $this_upstream;
							}
						}
					}
					else
					{
						$sAddUpstream = $this_upstream;
						$sAddUpstream2 = $this_unicom_ip;
					}			
						
					if(strlen($sAddUpstream)<=0 && strlen(sAddUpstream2)<=0)
					{							
						$sError = "无源站IP";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}								
					if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2))
					{
						$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
							
						continue;
					}
													
					$sError = "添加源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}			
			}
			else
			{
				$sError = "增加域名错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
			}
		}
		else if($aryProxyQuery["Return"]=="True")
		{
			$nProxyID = $aryProxyQuery["ProxyID"];
			
			$aryProxyEnable = FikApi_ProxyEnable($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$this_status);
			if($aryProxyEnable["Return"]=="True")
			{
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			else
			{
				$sError = "设置域名状态错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
			}
		}
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
		}	
	}
	else if($type==$PubDefine_TaskClearCache || $type==$PubDefine_TaskAdminClearCache)
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
			}

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
		$this_is_close		= mysql_result($result2,0,"is_close");

		if($this_is_close)
		{			
			// 服务器已经停用，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}
				
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;
				
		$aryClearCache = FikApi_CleanCache($sFikIP,$this_admin_port,$this_SessionID,$ext);
		if($aryClearCache["Return"]=="False")
		{
			if($aryClearCache["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryClearCache = FikApi_CleanCache($sFikIP,$this_admin_port,$this_SessionID,$ext);
				}				
				else
				{
					if($aryRelogin["ErrorNo"]==-1)
					{
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
					else
					{
						$sError = "";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
						
					continue;
				}
			}
		}
		else if($aryClearCache["Return"]=="True")
		{
		
		}
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}	
				
		if($aryClearCache["Return"]=="True")
		{
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;		
		}		
	}
	else if($type==$PubDefine_TaskDirClearCache)
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
			}

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
		$this_is_close		= mysql_result($result2,0,"is_close");

		if($this_is_close)
		{			
			// 服务器已经停用，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}		
		
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;
				
		$aryClearCache = FikApi_CleanCacheDir($sFikIP,$this_admin_port,$this_SessionID,$ext);
		if($aryClearCache["Return"]=="False")
		{
			if($aryClearCache["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryClearCache = FikApi_CleanCacheDir($sFikIP,$this_admin_port,$this_SessionID,$ext);
				}					
				else
				{
					if($aryRelogin["ErrorNo"]==-1)
					{
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
					else
					{
						$sError = "";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
						
					continue;
				}
			}
		}
		else if($aryClearCache["Return"]=="True")
		{
		
		}
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}		
		
		if($aryClearCache["Return"]=="True")
		{
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;		
		}		
	}	
	else if($type==$PubDefine_TaskAddProxy)
	{
		//获取域名信息
		$sql = "SELECT * FROM fikcdn_domain WHERE id=$domain_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			if(!$result2)
			{
				$sError = "查询域名错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);			
			continue;			
		}
	
		$this_domain_id	 = mysql_result($result2,0,"id");
		$this_hostname	 = mysql_result($result2,0,"hostname");
		$this_upstream	 = mysql_result($result2,0,"upstream");
		$this_unicom_ip	 = mysql_result($result2,0,"unicom_ip");
		$this_group_id	 = mysql_result($result2,0,"group_id");
		$this_buy_id	 = mysql_result($result2,0,"buy_id");
		$this_domain_note= mysql_result($result2,0,"note");					
		$use_transit_node= mysql_result($result2,0,"use_transit_node");	
		$this_status	 = mysql_result($result2,0,"status");
		$upstream_add_all= mysql_result($result2,0,"upstream_add_all");
		
		$SSLOpt	 		 = mysql_result($result2,0,"SSLOpt");
		$SSLCrtContent	 = mysql_result($result2,0,"SSLCrtContent");
		$SSLKeyContent	 = mysql_result($result2,0,"SSLKeyContent");
		$SSLExtraParams	 = mysql_result($result2,0,"SSLExtraParams");
		$UpsSSLOpt		 = mysql_result($result2,0,"UpsSSLOpt");
							
		//域名还在审核
		if($this_status==$PubDefine_HostStatusVerify)
		{			
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);

			continue;
		}
				
		//获取节点信息
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
		$result2 = mysql_query($sql,$db_link);
		if(!$result2 || mysql_num_rows($result2)<=0)
		{
			//echo "查询服务器错误，node_id=".$node_id."<br />";
			if(!$result2)
			{				
				$sError = "查询服务器错误";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}

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
		$this_is_close		= mysql_result($result2,0,"is_close");
		$fik_version	 	= mysql_result($result2,0,"fik_version");
		
		if($this_is_close)
		{			
			// 服务器已经停用，删除任务
			$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;			
		}
					
		$sFikIP = $this_ip;
		if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;
		
		$nProxyID=-1;
		$IsNeedAddProxy = false;
		
				
		$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
		if($show_version>="3.6.2")
		{
			$isNeedModify = false;
			$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
			print_r($aryProxyAdd);
			if($aryProxyAdd["Return"]=="False")
			{
				if($aryProxyAdd["ErrorNo"]==$FikCacheError_SessionHasOverdue)
				{
					$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
					if($aryRelogin["Return"]=="True")
					{
						$this_SessionID = $aryRelogin["SessionID"];
						
						$aryProxyAdd = FikApi_SSLProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
						if($aryProxyAdd["Return"]=="False")
						{
							if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
							{
								$nProxyID = $aryProxyAdd["ProxyID"];
								$isNeedModify = true;
							}
						}
					}					
					else
					{
						if($aryRelogin["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
						else
						{
							$sError = "";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
						}
							
						continue;
					}
				}
				else if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
				{
					$nProxyID = $aryProxyAdd["ProxyID"];
					$isNeedModify = true;
				}
			}
			
			if($aryProxyAdd["Return"]=="True")
			{
				$nProxyID = $aryProxyAdd["ProxyID"];
			}
			else if($aryProxyAdd["Return"]=="False")
			{
				if(!$isNeedModify)
				{
					$nErrorNo = $aryProxyAdd["ErrorNo"];
					if($nErrorNo == $FikCacheError_SSLCrtError)
					{
						$sError = "Fikker 返回错误，数字证书错误";
					}
					else if($nErrorNo == $FikCacheError_SSLKeyError)
					{
						$sError = "Fikker 返回错误，私钥错误";
					}
					else if($nErrorNo == $FikCacheError_CrtAndKeyNotMatch)
					{
						$sError = "Fikker 返回错误，数字证书和私钥不匹配";
					}
					else
					{
						$sError = "Fikker 返回错误，错误号：".$nErrorNo;
					}
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					continue;
				}
			}	
			else
			{
				$sError = "连接 Fikker 节点失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				continue;
			}		
			
			if($nProxyID>=0)
			{
				if($isNeedModify)
				{
					$aryProxyModify = FikApi_SSLProxyModify($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$this_hostname,$this_status,$this_domain_note,$SSLOpt,$SSLCrtContent,$SSLKeyContent,$SSLExtraParams);
						
					//获取源站列表
					$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
					if($aryUpstreamList["Return"]=="True")
					{
						$nNumOfLists = $aryUpstreamList["NumOfLists"];
						for($k=0;$k<$nNumOfLists;$k++)
						{
							$nNo = $aryUpstreamList["Lists"][$k]["NO"];
							$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
							
							//删除源站IP
							$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
							if($aryUpstreamDel["Return"]!="True")
							{
								continue;
							}
						}
					}					
				}
				
				//添加源站
				$sAddUpstream ="";
				$sAddUpstream2="";
				
				$this_upstream_ip="";
			
				$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
				$result5 = mysql_query($sql,$db_link);
				if($result5 && mysql_num_rows($result5)>0)
				{
					$sUpstream   = mysql_result($result5,0,"upstream");
					$sUpstream2  = mysql_result($result5,0,"upstream2");
					$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
					
					if($upstream_add_all2==0)
					{
						if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else if(strlen($this_ip)>0)
						{
							if(strlen($sUpstream)>0)
							{
								$sAddUpstream = $sUpstream;
							}					
							else
							{
								$sAddUpstream = $sUpstream2;
							}
						}
						else
						{
							if(strlen($sUpstream2)>0)
							{
								$sAddUpstream = $sUpstream2;
							}					
							else
							{
								$sAddUpstream = $sUpstream;
							}
						}
					}
					else
					{
						$sAddUpstream = $sUpstream;
						$sAddUpstream2 =$sUpstream2;
					}
					
					if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
					{							
						$sError = "无源站IP";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
					
					if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
					{
						$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
							
						continue;
					}
							
					if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
					{							
						$sError = "添加源站失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}						
				}
			
				if(!$result5)
				{
					$sError = "查询源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
			
				if($upstream_add_all==0)
				{
					if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else if(strlen($this_ip)>0)
					{
						if(strlen($this_upstream)>0)
						{
							$sAddUpstream = $this_upstream;
						}					
						else
						{
							$sAddUpstream = $this_unicom_ip;
						}
					}
					else
					{
						if(strlen($this_unicom_ip)>0)
						{
							$sAddUpstream = $this_unicom_ip;
						}					
						else
						{
							$sAddUpstream = $this_upstream;
						}
					}	
				}
				else
				{
					$sAddUpstream  = $this_upstream;
					$sAddUpstream2 = $this_unicom_ip;
				}
				
				if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
				{							
					$sError = "无源站IP";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}	
										
				if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
				{				
					$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
												
				$sError = "添加源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			$sError = "添加域名失败，节点返回错误";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);			
				
			continue;
		}		
		
		
		// 老版本的同步		
		$aryProxyAdd = FikApi_ProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_domain_note);
		if($aryProxyAdd["Return"]=="False")
		{
			if($aryProxyAdd["ErrorNo"]==$FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$this_SessionID = $aryRelogin["SessionID"];
					
					$aryProxyAdd = FikApi_ProxyAdd($sFikIP,$this_admin_port,$this_SessionID,$this_hostname,$this_domain_note);
				}					
				else
				{
					if($aryRelogin["ErrorNo"]==-1)
					{
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
					else
					{
						$sError = "";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
					}
						
					continue;
				}
			}
			else if($aryProxyAdd["ErrorNo"]==$FikCacheError_AddProxyHostHasExist)
			{
				$nProxyID = -100;
				$bQueryHostOk = false;
				
				//从 domain_stat_temp 表中查询域名
				$sql = "SELECT * FROM domain_stat_temp WHERE node_id=$node_id AND Host='$this_hostname'";
				$result3 = mysql_query($sql,$db_link);
				if($result3 && mysql_num_rows($result3)>0)
				{
					$nProxyID	= mysql_result($result3,0,"ProxyID");
					
					$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
					if($aryProxyQuery["Return"]=="True")
					{
						$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
						if($sQueryHost==$this_hostname)
						{
							$bQueryHostOk=true;
						}
					}
					else if($aryProxyQuery["Return"]=="False")
					{
						if($aryProxyQuery["ErrorNo"]==$FikCacheError_SessionHasOverdue)
						{
							$aryRelogin = FikApi_Relogin($node_id,$sFikIP,$this_admin_port,$this_password,$db_link);
							if($aryRelogin["Return"]=="True")
							{
								$this_SessionID = $aryRelogin["SessionID"];
								
								//重新获取域名列表
								$aryProxyQuery = FikApi_ProxyQuery($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);			
								if($aryProxyQuery["Return"]=="True")
								{
									$sQueryHost = $aryProxyQuery["Lists"][0]["Host"];
									if($sQueryHost==$this_hostname)
									{
										$bQueryHostOk=true;
									}
								}
							}
							else
							{
								if($aryRelogin["ErrorNo"]==-1)
								{
									$sError = "连接 Fikker 节点失败";
									$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
									$result2 = mysql_query($sql,$db_link);
								}
								else
								{
									$sError = "";
									$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
									$result2 = mysql_query($sql,$db_link);
								}
									
								continue;
							}
						}
						else
						{	
							/*
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
							*/
						}
					}		
					else
					{
						/*
						$sError = "连接 Fikker 节点失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
						*/
					}				
				}			
				
				if($bQueryHostOk && $nProxyID>=0)
				{		
					echo "add proxy --------- find 0k";
					
					//获取源站列表
					$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
					if($aryUpstreamList["Return"]=="True")
					{
						$nNumOfLists = $aryUpstreamList["NumOfLists"];
						for($k=0;$k<$nNumOfLists;$k++)
						{
							$nNo = $aryUpstreamList["Lists"][$k]["NO"];
							$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
							
							//删除源站IP
							$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
							if($aryUpstreamDel["Return"]!="True")
							{
								continue;
							}
						}		
						
						$IsNeedAddProxy = true;
					}
				}
				else
				{	
					echo "add proxy --------- find failed.";
					$aryProxyQuery = FikApi_ProxyQueryDomain($sFikIP,$this_admin_port,$this_SessionID,$this_hostname);
					if($aryProxyQuery["Return"]=="True")
					{			
						$nProxyID = $aryProxyQuery["ProxyID"];
						
						echo "  add proxy --------- FikApi_ProxyQueryDomain nProxyID=".$nProxyID .', ';
							
						//获取源站列表
						$aryUpstreamList = FikApi_UpstreamList($sFikIP,$this_admin_port,$this_SessionID,$nProxyID);
						if($aryUpstreamList["Return"]=="True")
						{
							$nNumOfLists = $aryUpstreamList["NumOfLists"];
							for($k=0;$k<$nNumOfLists;$k++)
							{
								$nNo = $aryUpstreamList["Lists"][$k]["NO"];
								$nUpstreamID = $aryUpstreamList["Lists"][$k]["UpstreamID"];
								
								//删除源站IP
								$aryUpstreamDel = FikApi_UpstreamDel($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$nUpstreamID);
								if($aryUpstreamDel["Return"]!="True")
								{
									continue;
								}
							}		
							
							$IsNeedAddProxy = true;
						}
					}
					else if($aryProxyQuery["Return"]=="False")
					{
						if($aryProxyQuery["ErrorNo"]==-1)
						{
							$sError = "连接 Fikker 节点失败";
							$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
							$result2 = mysql_query($sql,$db_link);
							
							continue;
						}				
						
						$sError = "查询节点域名失败";
						$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
						$result2 = mysql_query($sql,$db_link);
						
						continue;
					}
				}
			}
		}
		else if($aryProxyAdd["Return"]=="True")
		{
		
		}
		else
		{
			$sError = "连接 Fikker 节点失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
		
		if($aryProxyAdd["Return"]=="True")
		{
			$nProxyID = $aryProxyAdd["ProxyID"];
					
			$IsNeedAddProxy = true;
		}
		
		$sAddUpstream ="";
		$sAddUpstream2="";
		
		if($IsNeedAddProxy && $nProxyID>=0)
		{
			$this_upstream_ip="";
			
			$sql = "SELECT * FROM fikcdn_upstream WHERE node_id='$node_id' AND hostname='$this_hostname'";
			$result5 = mysql_query($sql,$db_link);
			if($result5 && mysql_num_rows($result5)>0)
			{
				$sUpstream   = mysql_result($result5,0,"upstream");
				$sUpstream2  = mysql_result($result5,0,"upstream2");
				$upstream_add_all2= mysql_result($result5,0,"upstream_add_all");
				
				if($upstream_add_all2==0)
				{
					if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
					{
						if(strlen($sUpstream)>0)
						{
							$sAddUpstream = $sUpstream;
						}
						else
						{
							$sAddUpstream = $sUpstream2;
						}
					}
					else if(strlen($this_ip)>0)
					{
						if(strlen($sUpstream)>0)
						{
							$sAddUpstream = $sUpstream;
						}					
						else
						{
							$sAddUpstream = $sUpstream2;
						}
					}
					else
					{
						if(strlen($sUpstream2)>0)
						{
							$sAddUpstream = $sUpstream2;
						}					
						else
						{
							$sAddUpstream = $sUpstream;
						}
					}
				}
				else
				{
					$sAddUpstream = $sUpstream;
					$sAddUpstream2 =$sUpstream2;
				}
				
				if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
				{							
					$sError = "无源站IP";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}
				
				if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
				{
					$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
						
					continue;
				}
						
				if(strlen($sAddUpstream)>0 || strlen($sAddUpstream2)>0)
				{							
					$sError = "添加源站失败";
					$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
					$result2 = mysql_query($sql,$db_link);
					
					continue;
				}						
			}
			
			if(!$result5)
			{
				$sError = "查询源站失败";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}
			
			if($upstream_add_all==0)
			{
				if(strlen($this_ip)>0 && strlen($node_unicom_ip)>0)
				{
					if(strlen($this_upstream)>0)
					{
						$sAddUpstream = $this_upstream;
					}
					else
					{
						$sAddUpstream = $this_unicom_ip;
					}
				}
				else if(strlen($this_ip)>0)
				{
					if(strlen($this_upstream)>0)
					{
						$sAddUpstream = $this_upstream;
					}					
					else
					{
						$sAddUpstream = $this_unicom_ip;
					}
				}
				else
				{
					if(strlen($this_unicom_ip)>0)
					{
						$sAddUpstream = $this_unicom_ip;
					}					
					else
					{
						$sAddUpstream = $this_upstream;
					}
				}	
			}
			else
			{
				$sAddUpstream  = $this_upstream;
				$sAddUpstream2 = $this_unicom_ip;
			}
			
			if(strlen($sAddUpstream)<=0 && strlen($sAddUpstream2)<=0)
			{							
				$sError = "无源站IP";
				$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
				
				continue;
			}	
									
			if(ExecuteTask_AddUpsream($sFikIP,$this_admin_port,$this_SessionID,$nProxyID,$sAddUpstream,$sAddUpstream2,$UpsSSLOpt))
			{				
				$sql = "DELETE FROM fikcdn_task WHERE id=$task_id";
				$result2 = mysql_query($sql,$db_link);
					
				continue;
			}
											
			$sError = "添加源站失败";
			$sql = "UPDATE fikcdn_task SET result_str ='$sError' WHERE id=$task_id";
			$result2 = mysql_query($sql,$db_link);
			
			continue;
		}
	}
}

$timenow = time();
$sql = "UPDATE fikcdn_params SET value='0',time='$timenow' WHERE name='execute_task_enter'";
$result = mysql_query($sql,$db_link);	
if(!$result)
{
	mysql_close($db_link);
	exit();
};

mysql_close($db_link);

function ExecuteTask_AddProxy($sFikIP,$nAdminPort,$sSessionID,$sHost,$sUpstream1,$sUpstream2,$sNote)
{
	$aryProxyAdd = FikApi_ProxyAdd($sFikIP,$nAdminPort,$sSessionID,$sHost,$sNote);
	if($aryUpstreamAdd["Return"]=="True")
	{
		$nProxyID = $aryProxyAdd["ProxyID"];
		$bAddUpStreamOk = false;
		
		//增加源站
		if(strlen($sUpstream1))
		{
			$aryUpstreamAdd = FikApi_UpstreamAdd($sFikIP,$nAdminPort,$sSessionID,$nProxyID,$sUpstream1,"");
			if($aryUpstreamAdd["Return"]=="True")
			{
				$bAddUpStreamOk = true;	
			}
		}
		
		//增加源站
		if(strlen($sUpstream2))
		{
			$aryUpstreamAdd = FikApi_UpstreamAdd($sFikIP,$nAdminPort,$sSessionID,$nProxyID,$sUpstream2,"");
			if($aryUpstreamAdd["Return"]=="True")
			{
				$bAddUpStreamOk=true;	
			}
		}
		
		if($bAddUpStreamOk)
		{
			return 0;
		}	
		
		return -3;
	}
	else if($aryUpstreamAdd["Return"]=="False")
	{
		return -2;
	}
	
	return -1;
}

function ExecuteTask_AddUpsream($sFikIP,$nAdminPort,$sSessionID,$nProxyID,$sUpstream1,$sUpstream2,$UpsSSLOpt)
{
	$bAddUpStreamOk = false;
	
	//增加源站
	if(strlen($sUpstream1)>0)
	{
		$aryStr = explode(";",$sUpstream1);
		for($iiii=0;$iiii<count($aryStr);$iiii++)
		{
			$aryUpstreamAdd = FikApi_UpstreamAdd($sFikIP,$nAdminPort,$sSessionID,$nProxyID,$aryStr[$iiii],"",$UpsSSLOpt);
			if($aryUpstreamAdd["Return"]=="True")
			{
				$bAddUpStreamOk = true;	
			}
		}
	}
	
	//增加源站
	if(strlen($sUpstream2)>0)
	{
		$aryStr = explode(";",$sUpstream2);
		for($iiii=0;$iiii<count($aryStr);$iiii++)
		{
			$aryUpstreamAdd = FikApi_UpstreamAdd($sFikIP,$nAdminPort,$sSessionID,$nProxyID,$aryStr[$iiii],"",$UpsSSLOpt);
			if($aryUpstreamAdd["Return"]=="True")
			{
				$bAddUpStreamOk = true;	
			}
		}
	}
	
	if($bAddUpStreamOk)
	{
		return true;
	}	
	
	return false;
}

?>
