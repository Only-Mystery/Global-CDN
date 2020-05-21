<?php
	
include_once('../db/db.php');
include_once('../function/pub_function.php');
include_once('../function/define.php');
include_once("function_admin.php");

//是否登录
if(!FuncAdmin_IsLogin())
{
	$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoLogin);
	PubFunc_EchoJsonAndExit($aryResult,NULL);
}

$fikcdn_admin_power = $_SESSION['fikcdn_admin_power'];

$sMod 	 	 = isset($_GET['mod'])?$_GET['mod']:'';
$sAction 	 = isset($_GET['action'])?$_GET['action']:'';

if($sMod=="product")
{
	if($sAction=="add")
	{
		$txtName = isset($_POST['name'])?$_POST['name']:'';	
		$nDataFlow   = isset($_POST['data_flow'])?$_POST['data_flow']:'';	
		$grpid = isset($_POST['grpid'])?$_POST['grpid']:'';	
		$nDomainNum = isset($_POST['domain_num'])?$_POST['domain_num']:'';	
		$nPrice= isset($_POST['price'])?$_POST['price']:'';	
		$statusSelect = isset($_POST['is_online'])?$_POST['is_online']:'';
		$txtBackup	 = isset($_POST['backup'])?$_POST['backup']:'';		
		$sDnsCName = isset($_POST['cname'])?$_POST['cname']:'';	
		
		if($statusSelect!=1)
		{
			$statusSelect=0;
		}
			
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($nDataFlow) || !is_numeric($grpid) || !is_numeric($nDomainNum) || !is_numeric($nPrice))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($nPrice<=0 || $nDataFlow<=0 ||$nDomainNum<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if( strlen(txtName)<=0 || strlen(txtName)>64 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
	
		if( strlen($txtBackup) > 128 || strlen($sDnsCName) > 64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		

		$txtName = htmlspecialchars($txtName);
		$txtBackup = htmlspecialchars($txtBackup);
		$sDnsCName = htmlspecialchars($sDnsCName);
		
		$admin_username =$_SESSION['fikcdn_admin_username'];
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$txtName = mysql_real_escape_string($txtName);
			$txtBackup = mysql_real_escape_string($txtBackup);
			$sDnsCName = mysql_real_escape_string($sDnsCName);
			
			$sql="SELECT * FROM fikcdn_group WHERE id='$grpid'";		
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{	
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'ErrorMsg'=>'添加产品套餐失败，服务器组不存在。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$timenow = time();
			//插入充值记录
			$sql="INSERT INTO fikcdn_product(id,name,price,data_flow,domain_num,is_online,begin_time,note,is_checks,group_id,dns_cname)
				VALUES(NULL,'$txtName','$nPrice','$nDataFlow','$nDomainNum','$statusSelect','$timenow','$txtBackup','1','$grpid','$sDnsCName')";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{				
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrInsert,'ErrorMsg'=>'添加产品套餐失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			$re_id = mysql_insert_id($db_link);
			
			$aryResult = array('Return'=>'True','id'=>$re_id);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'添加产品套餐失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}	
	}	
	else if($sAction=="modify")
	{
		$pid = isset($_POST['id'])?$_POST['id']:'';	
		$txtName = isset($_POST['name'])?$_POST['name']:'';	
		$nDataFlow   = isset($_POST['data_flow'])?$_POST['data_flow']:'';	
		$nDomainNum = isset($_POST['domain_num'])?$_POST['domain_num']:'';	
		$statusSelect = isset($_POST['is_online'])?$_POST['is_online']:'';
		$nPrice= isset($_POST['price'])?$_POST['price']:'';	
		$sDnsCName = isset($_POST['cname'])?$_POST['cname']:'';	
		$txtBackup	 = isset($_POST['backup'])?$_POST['backup']:'';		
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
				
		if(!is_numeric($nDataFlow)|| !is_numeric($nDomainNum) || !is_numeric($nPrice) || !is_numeric($pid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		if($statusSelect!=1)
		{
			$statusSelect=0;
		}
		
		if($nPrice<=0 || $nDataFlow<=0 ||$nDomainNum<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
				
		if( strlen(txtName)<=0 || strlen(txtName)>64 )
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
	
		if( strlen($txtBackup) > 128 || strlen($sDnsCName) > 64)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}		

		$txtName = htmlspecialchars($txtName);
		$txtBackup = htmlspecialchars($txtBackup);
		$sDnsCName = htmlspecialchars($sDnsCName);
		
		$admin_username =$_SESSION['fikcdn_admin_username'];
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$txtName = mysql_real_escape_string($txtName);
			$txtBackup = mysql_real_escape_string($txtBackup);
			$sDnsCName = mysql_real_escape_string($sDnsCName);
			
			$timenow = time();
			//插入充值记录
			$sql="UPDATE fikcdn_product SET name='$txtName',price='$nPrice',dns_cname='$sDnsCName',data_flow='$nDataFlow',domain_num='$nDomainNum',is_online='$statusSelect',note='$txtBackup' WHERE id='$pid'";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{			
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'修改产品套餐信息失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			$aryResult = array('Return'=>'True','id'=>$pid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}	
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'修改产品套餐信息失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);			
		}	
	
	}
	else if($sAction=="del")
	{
		$pid = isset($_POST['id'])?$_POST['id']:'';	
		
		if($fikcdn_admin_power!=10)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrNoPower,'ErrorMsg'=>'对不起，您无系统操作权限。');
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		if(!is_numeric($pid) || strlen($pid)<=0)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam,'ErrorMsg'=>'参数错误');
			PubFunc_EchoJsonAndExit($aryResult,NULL);		
		}
		
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "select * from fikcdn_order where product_id=$pid;";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除产品套餐失败，数据库查询错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			if(mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除产品套餐失败，请先删除此套数下的所有订单。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);			
			}
			
			$sql = "select * from fikcdn_buy where product_id=$pid;";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除产品套餐失败，数据库查询错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}
			
			if(mysql_num_rows($result)>0)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除产品套餐失败，请先删除此产品下的所有已经购买的套餐。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);			
			}
			
			$sql = "delete from fikcdn_product where id=$pid";
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrUpdate,'ErrorMsg'=>'删除产品套餐失败，操作数据库错误。');
				PubFunc_EchoJsonAndExit($aryResult,$db_link);
			}		
			
			$aryResult = array('Return'=>'True','id'=>$pid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);			
		}		
		else
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'ErrorMsg'=>'删除产品套餐失败，连接数据库错误。');
			PubFunc_EchoJsonAndExit($aryResult,$db_link);			
		}			
				
	}
}

?>
