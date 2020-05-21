<?php
include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once('../function/define.php');
include_once('function_admin.php');

//是否登录
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
	exit();
}	

$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod=="logging")
{
	if($sAction=="redirect")
	{
		$sID 		= isset($_GET['id'])?$_GET['id']:'';
		
		if(!is_numeric($sID))
		{	
			exit();
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_node WHERE id=$sID";
			$result = mysql_query($sql,$db_link);
			if($result )
			{
				$row_count=mysql_num_rows($result);
				if($row_count>0)
				{
					$i =0;
		
					$id  			= mysql_result($result,$i,"id");	
					$name   		= mysql_result($result,$i,"name");	
					$ip  		 	= mysql_result($result,$i,"ip");
					$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");
					$port   		= mysql_result($result,$i,"port");
					$admin_port   	= mysql_result($result,$i,"admin_port");	
					$add_time   	= mysql_result($result,$i,"add_time");
					$fik_version   	= mysql_result($result,$i,"fik_version");
					$fik_VersionExt	= mysql_result($result,$i,"version_ext");		
					$auth_domain   	= mysql_result($result,$i,"auth_domain");
					$groupid	   	= mysql_result($result,$i,"groupid");
					$sPasswd	   	= mysql_result($result,$i,"password");
					$fik_session	= mysql_result($result,$i,"SessionID");
					
					$sFikIP = $ip;
					if(strlen($sFikIP)<=0)
					{
						$sFikIP = $unicom_ip;
					}
					
					if($FikConfig_KeeperLogin)
					{
						$fikker_user = "keeper";
					}
					else
					{
						$fikker_user = "admin";
					}
					
					//获取Fikker的认证信息
					$AryAuthResult = FikApi_GetAuth($sFikIP,$admin_port,$fik_session);
					$AryAuthResult = json_decode($AryAuthResult,true); 
					if($AryAuthResult["Return"]=="True")
					{									
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
						$sql = "UPDATE fikcdn_node SET auth_domain='$auth_domain' WHERE id='$sID'";
						if(!mysql_query($sql,$db_link))
						{
						}
						
						$url = "Location: http://".$sFikIP.":".$admin_port."/fikker/login-api.htm?"."SessionID=".urlencode($fik_session)."&usertype=".$fikker_user."&version=".urlencode($fik_version)."&VersionExt=".urlencode($fik_VersionExt);
						//var_dump($url);
						header($url);
						exit();
					}
					
					//登录Fikker
					$AryResult = fikapi_Login($sFikIP,$admin_port,$sPasswd);
					$AryResult = json_decode($AryResult,true);
					if (!is_array($AryResult))
					{
						$sql = "UPDATE fikcdn_node SET status=2 WHERE id='$id'";
						
						$result = mysql_query($sql,$db_link);
						mysql_close($db_link);
						
						$url = "Location: http://".$sFikIP.":".$admin_port."/fikker/";
						header($url);
						exit();
					}
					
					if($AryResult["Return"]!="True")
					{		
						if($AryResult["ErrorNo"] == $FikCacheError_PasswordError)
						{
							$sql = "UPDATE fikcdn_node SET status=3 WHERE id='$id'";
							$result = mysql_query($sql,$db_link);
						}	
						mysql_close($db_link);
						
						$url = "Location: http://".$sFikIP.":".$admin_port."/fikker/";
						header($url);
						exit();
					}
					
					$fik_version 		= $AryResult["Version"];
					$fik_VersionExt		= $AryResult["VersionExt"];
					$fik_session 		= $AryResult["SessionID"];
					$fik_LastLoginTime 	= $AryResult["LastLoginTime"];
					
					$sql = "UPDATE fikcdn_node SET status=1,fik_version='$fik_version',SessionID='$fik_session',fik_LastLoginTime='$fik_LastLoginTime',version_ext='$fik_VersionExt' WHERE id='$id'";	
					mysql_query($sql,$db_link);
					mysql_close($db_link);
					
					$url = "Location: http://".$sFikIP.":".$admin_port."/fikker/login-api.htm?"."SessionID=".urlencode($fik_session)."&usertype=".$fikker_user."&version=".urlencode($fik_version)."&VersionExt=".urlencode($fik_VersionExt);
					//var_dump($url);
					header($url);
					exit();
				}
			}
			mysql_close($db_link);
		}
		else
		{
			$aryResult = array('Return'=>'false','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}
}



?>
