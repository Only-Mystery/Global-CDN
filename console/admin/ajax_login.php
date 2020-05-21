<?php
if(!isset($_SESSION)){  
   session_start();  
}

include_once('../db/db.php');
include_once('../function/fik_api.php');
include_once("function_admin.php");

$sMod 	 	 = isset($_GET['mod'])?$_GET['mod']:'';
$sAction 	 = isset($_GET['action'])?$_GET['action']:'';
$sUsername 	 = isset($_POST['username'])?$_POST['username']:'';
$sPassword	 = isset($_POST['password'])?$_POST['password']:'';	

if($sMod=='login')
{
	if($sAction=="logging")
	{
		$sUsername 	 = isset($_POST['username'])?$_POST['username']:'';
		$sPassword	 = isset($_POST['password'])?$_POST['password']:'';	
		$sCheckCode	 = isset($_POST['code'])?$_POST['code']:'';
		
		if(strlen($sUsername)<=0 || strlen($sUsername) > 64 || strlen($sPassword)> 64 || strlen($sPassword)<=0 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$sSessionCheckCode = $_SESSION['CheckCode'];
		
		if($FikConfig_IsUserCheckCode)
		{
			if(strcasecmp($sSessionCheckCode,$sCheckCode)!=0 && strlen($sCheckCode)>0)
			{			
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrCheckCode);
				PubFunc_EchoJsonAndExit($aryResult,NULL);
			}
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			//echo mysql_error();
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		$escape_user = mysql_real_escape_string($sUsername); 
		$escape_passwd = mysql_real_escape_string($sPassword); 
		
		$sql = "SELECT * FROM fikcdn_admin WHERE username='$escape_user' AND password='$escape_passwd'"; 
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$this_username  = mysql_result($result,0,"username");
		$enable  = mysql_result($result,0,"enable");	
		$sNick   = mysql_result($result,0,"nick");	
		$power   = mysql_result($result,0,"power");
		
		// 区分大小写
		if($this_username != $escape_user)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		// 是否允许登录
		if($enable != 1)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrForbidLogin);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$login_time = time();
		
		$client_ip = PubFunc_GetRemortIP(); //$_SERVER["REMOTE_ADDR"]; 
		$sql = "UPDATE fikcdn_admin SET last_login_time='$login_time',last_login_ip='$client_ip',login_count=login_count+1 WHERE username='$escape_user';";
		mysql_query($sql,$db_link);
		
		//插入管理员登录日志
		$sql = "INSERT INTO fikcdn_login_log(id,username,login_ip,login_time,status,type) VALUES(NULL,'$escape_user','$client_ip','$login_time',1,$PubDefine_AdminLoginLog)";
		mysql_query($sql,$db_link);
		
		mysql_close($db_link);
			
		$_SESSION['fikcdn_admin_IsLogin']	=true;
		$_SESSION['fikcdn_admin_username']	=$escape_user;
		$_SESSION['fikcdn_admin_nick']		=$sNick;
		$_SESSION['fikcdn_admin_power']		=$power;
		
		/*
		if($savepasswd=="true")
		{
			//setcookie("fikcdn_admin_username", $sUsername, time()+8640000,'$FikCdnCookiePaht');
			//setcookie("fikcdn_admin_password", $sPassword, time()+86400,'$FikCdnCookiePaht');
			//setcookie("fikcdn_auth",
		}
		else
		{
			//setcookie("fikcdn_admin_username", $sUsername, time()+86400,$FikCdnCookiePaht);
			//setcookie("fikcdn_admin_password");
		}
		*/
		//登录成功
		//setcookie("fikcdn_admin_remember", $savepasswd, time()+8640000,$FikCdnCookiePaht);
		$auth_random = PubFunc_CreateRandStr(32);
		setcookie("fikcdn_admin_auth", $auth_random,0,$FikCdnCookiePaht);
		$_SESSION['fikcdn_admin_auth'] = $auth_random;
		
		$_SESSION['CheckCode']="";
		
		$aryResult = array('Return'=>'True','username'=>$sUsername,'auth_random'=>$auth_random);
		PubFunc_EchoJsonAndExit($aryResult,NULL);
	}
	else if($sAction=="logout")
	{
		$sUsername 	= $_SESSION['fikcdn_admin_username'];
		
		$_SESSION['fikcdn_admin_IsLogin']	=false;
		$_SESSION['fikcdn_admin_username']	="";
		$_SESSION['fikcdn_admin_nick']		="";
		$_SESSION['fikcdn_admin_power']		="";
		
		setcookie("fikcdn_admin_username", $sUsername, time()+8640000,$FikCdnCookiePaht);
		setcookie("fikcdn_admin_password", "", time()+86400,$FikCdnCookiePaht);		
		setcookie("fikcdn_admin_auth", "",0,$FikCdnCookiePaht);
		$_SESSION['fikcdn_admin_auth'] = "";
	}
	else if($sAction=="is_login")
	{
		if(!FuncAdmin_IsLogin())
		{
			echo '0';
		}
		else
		{
			echo '1';
		}
	}	
}

?>
