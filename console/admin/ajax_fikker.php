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

$sMod  		= isset($_GET['mod'])?$_GET['mod']:'';
$sAction  	= isset($_GET['action'])?$_GET['action']:'';
if($sMod == "fiknode")
{
	if($sAction=="realtime")
	{
		$nodeid 	= isset($_POST['nodeid'])?$_POST['nodeid']:'';
		
		if(!is_numeric($nodeid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'NodeID'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		// 是否重复添加节点
		$sql = "SELECT * FROM fikcdn_node WHERE id='$nodeid';";
		$result = mysql_query($sql,$db_link);
		if(!$result && mysql_num_rows($result)<=0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'NodeID'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$node_ip  		= mysql_result($result,0,"ip");
		$node_unicom_ip	= mysql_result($result,$i,"unicom_ip");
		$node_port		= mysql_result($result,0,"port");
		$admin_port   	= mysql_result($result,0,"admin_port");
		$status	   		= mysql_result($result,0,"status");
		$password		= mysql_result($result,0,"password");
		$SessionID		= mysql_result($result,0,"SessionID");
		
		$sFikIP = $node_ip;
		if(strlen($sFikIP)<=0)
		{
			$sFikIP = $node_unicom_ip;
		}
		
		$aryRealtimeList = fikapi_realtimelist($sFikIP,$admin_port,$SessionID);
		if($aryRealtimeList["Return"]=="False")
		{			
			if($aryRealtimeList["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = fikapi_relogin($nodeid,$sFikIP,$admin_port,$password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$SessionID = $aryRelogin["SessionID"];
					$aryRealtimeList = fikapi_realtimelist($sFikIP,$admin_port,$SessionID);
				}
				else
				{
					if($aryRelogin["ErrorNo"]==-1)
					{
						$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'NodeID'=>$nodeid);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);
					}
					else if($aryRelogin["ErrorNo"]==-2)
					{
						if($aryRelogin["FikErrorNo"]==$FikCacheError_PasswordError)
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrFikPasswd,'NodeID'=>$nodeid);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);	
						}
						else if($aryRelogin["FikErrorNo"]==$FikCacheError_ServerBusy)
						{			
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrServerBusy,'NodeID'=>$nodeid);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);						
						}
						else
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'NodeID1'=>$nodeid,'FikErrorNo'=>$aryRelogin["FikErrorNo"]);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);		
						}
					}
				}
			}
		}
		
		if($aryRealtimeList["Return"]=="True")
		{
			/*
			$CurrentUserConnections = $aryFikResult['CurrentUserConnections'];
			$CurrentUpstreamConnections = $aryFikResult['CurrentUpstreamConnections'];
			$AllUsedMemSize = $aryFikResult['AllUsedMemSize'];
			$CacheUsedMemSize = $aryFikResult['CacheUsedMemSize'];
			$NumOfCaches = $aryFikResult['NumOfCaches'];
			$TotalSendKB = $aryFikResult['TotalSendKB'];
			$TotalRecvKB = $aryFikResult['TotalRecvKB'];
			$NumOfCachedSessions = $aryFikResult['NumOfCachedSessions'];
			$NumOfPublicCaches = $aryFikResult['NumOfPublicCaches'];
			$NumOfMemberCaches = $aryFikResult['NumOfMemberCaches'];
			$NumOfVisitorCaches = $aryFikResult['NumOfVisitorCaches'];
			$PublicCacheUsedMemSize = $aryFikResult['PublicCacheUsedMemSize'];
			$MemberCacheUsedMemSize = $aryFikResult['MemberCacheUsedMemSize'];
			$VisitorCacheUsedMemSize = $aryFikResult['VisitorCacheUsedMemSize'];
			$TotalSendToResponseKB = $aryFikResult['TotalSendToResponseKB'];
			$TotalRecvFromResponseKB = $aryFikResult['TotalRecvFromResponseKB'];
			*/
			$aryRealtimeList["NodeID"]=$nodeid;
			PubFunc_EchoJsonAndExit($aryRealtimeList,$db_link);	
		}
		else if($aryRealtimeList["Return"]=="False")
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'NodeID2'=>$nodeid,'FikErrorNo'=>$aryRealtimeList["ErrorNo"]);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	
		$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'NodeID'=>$nodeid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);
	}
	else if($sAction=="auth")
	{
		$nodeid 	= isset($_POST['nodeid'])?$_POST['nodeid']:'';
		
		if(!is_numeric($nodeid))
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrParam);
			PubFunc_EchoJsonAndExit($aryResult,NULL);	
		}
		
		$db_link = FikCDNDB_Connect();
		if(!$db_link)
		{
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnectDB,'NodeID'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,NULL);
		}
		
		// 是否重复添加节点
		$sql = "SELECT * FROM fikcdn_node WHERE id='$nodeid';";
		$result = mysql_query($sql,$db_link);
		if(!$result && mysql_num_rows($result)<=0)
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrQuery,'NodeID'=>$nodeid);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
		
		$node_ip  		= mysql_result($result,0,"ip");
		$node_unicom_ip	= mysql_result($result,$i,"unicom_ip");
		$node_port		= mysql_result($result,0,"port");
		$admin_port   	= mysql_result($result,0,"admin_port");
		$status	   		= mysql_result($result,0,"status");
		$password		= mysql_result($result,0,"password");
		$SessionID		= mysql_result($result,0,"SessionID");
		
		$sFikIP = $node_ip;
		if(strlen($sFikIP)<=0)
		{
			$sFikIP = $node_unicom_ip;
		}
		
		$aryAuth = FikApi_GetAuth($sFikIP,$admin_port,$SessionID);
		$aryAuth = json_decode($aryAuth,true); 
		if($aryAuth["Return"]=="False")
		{			
			if($aryAuth["ErrorNo"] == $FikCacheError_SessionHasOverdue)
			{
				$aryRelogin = fikapi_relogin($nodeid,$sFikIP,$admin_port,$password,$db_link);
				if($aryRelogin["Return"]=="True")
				{
					$SessionID = $aryRelogin["SessionID"];
					$aryAuth = FikApi_GetAuth($sFikIP,$admin_port,$SessionID);
					$aryAuth = json_decode($aryAuth,true); 
				}
				else
				{
					if($aryRelogin["ErrorNo"]==-1)
					{
						$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'NodeID'=>$nodeid);
						PubFunc_EchoJsonAndExit($aryResult,$db_link);
					}
					else if($aryRelogin["ErrorNo"]==-2)
					{
						if($aryRelogin["FikErrorNo"]==$FikCacheError_PasswordError)
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrFikPasswd,'NodeID'=>$nodeid);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);
						}
						else if($aryRelogin["FikErrorNo"]==$FikCacheError_ServerBusy)
						{			
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrServerBusy,'NodeID'=>$nodeid);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);					
						}
						else
						{
							$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'NodeID1'=>$nodeid,'FikErrorNo'=>$aryRelogin["FikErrorNo"]);
							PubFunc_EchoJsonAndExit($aryResult,$db_link);		
						}
					}
				}
			}
		}
		
		if($aryAuth["Return"]=="True")
		{
			$aryAuth["NodeID"]=$nodeid;
			PubFunc_EchoJsonAndExit($aryAuth,$db_link);	
		}
		else if($aryAuth["Return"]=="False")
		{	
			$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrReturnFalse,'NodeID2'=>$nodeid,'FikErrorNo'=>$aryAuth["ErrorNo"]);
			PubFunc_EchoJsonAndExit($aryResult,$db_link);
		}
	
		$aryResult = array('Return'=>'False','ErrorNo'=>$PubDefine_ErrConnFik,'NodeID'=>$nodeid);
		PubFunc_EchoJsonAndExit($aryResult,$db_link);	
	}
}
//echo "mod=".$sMod."&action=".$sAction;


?>
