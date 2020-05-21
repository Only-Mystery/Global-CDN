<?php
if(!isset($_SESSION)){
   session_start();  
}
//判断当前请求用户是否登录
function FuncAdmin_IsLogin()
{
	$username 					=$_SESSION['fikcdn_admin_username'];
	$IsLogin 					=$_SESSION['fikcdn_admin_IsLogin'];
	$fikcdn_admin_auth 			=$_SESSION['fikcdn_admin_auth'];
	$cookie_fikcdn_admin_auth 	=$_COOKIE['fikcdn_admin_auth'];
	if(strlen($username)>0 && $IsLogin && ($fikcdn_admin_auth==$cookie_fikcdn_admin_auth))
	{
		return true;
	}
	
	//echo 'username='.$username.'<br />';
	//echo 'fikcdn_admin_auth='.$fikcdn_admin_auth.'<br />';
	//echo 'cookie_fikcdn_admin_auth='.$cookie_fikcdn_admin_auth.'<br />';
	
	return false;
}

function FuncAdmin_LocationLogin()
{
	header("Location:login.php");
	exit();
}

function FuncAdmin_KBToString($nKB)
{
	if($nKB<0)
	{
		$nKB = 0;
	}

	if($nKB<1024)
	{
		$sReturn=nKB."KB";
		return $sReturn;
	}

	$value =$nKB / (1024);
	if($value<1024)
	{
		$sReturn=$value."MB";
		return $sReturn;
	}

	$value = $nKB/(1024*1024);

	$sReturn=$value."GB";
	return $sReturn;
}


function FuncAdmin_MBToString($nKB)
{
	if($nKB<0)
	{
		$nKB = 0;
	}

	if($nKB<1024)
	{
		$sReturn=$nKB." MB";
		return $sReturn;
	}

	$value =round($nKB / (1024),2);
	if($value<1024)
	{
		$sReturn=$value." GB";
		return $sReturn;
	}
	
	$value =round($nKB / (1024*1024),2);
	if($value<1024)
	{
		$sReturn=$value." TB";
		return $sReturn;
	}

	$sReturn=$value." TB";
	return $sReturn;
}

?>