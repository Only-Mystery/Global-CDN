<?php
include_once("./head.php");
include_once('../function/fik_api.php');
$node_id 		= isset($_GET['node_id'])?$_GET['node_id']:'';
?>
<script type="text/javascript">
function FikCdn_AddRCacheBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rcache_add.php?node_id="+txtSelectNode;
	showMSGBOX('',550,300,BT,BL,120,boxURL,'添加拒绝缓存:');
}
var ___nRid;
var ___nNodeID;

function FikCdn_ModifyRCacheBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rcache_modify.php?node_id="+txtSelectNode+"&rid="+Rid;
	showMSGBOX('',550,300,BT,BL,120,boxURL,'修改拒绝缓存:');
}

function FikCdn_DelRCacheBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?6.3";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelRCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rcache&action=del";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	AjaxBasePost("rcache","del","POST",postURL,postStr);	
}


function FikCdn_DelRCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?6.4";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	}else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}else{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}	
}

function FikCdn_UpRCacheBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?6.5";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_UpRCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rcache&action=up";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	
	AjaxBasePost("rcache","up","POST",postURL,postStr);	
}

function FikCdn_UpRCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?6.7";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	}else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}else{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}	
}

function FikCdn_DownRCacheBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?6.6";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}
function FikCdn_DownRCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rcache&action=down";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	
	AjaxBasePost("rcache","down","POST",postURL,postStr);	
}


function FikCdn_DownRCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?6.8";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	}else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}else{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}	
}

function FikCdn_RefreshRCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_rcache.php?node_id="+txtSelectNode;
	return;
	
	/*
	var txtSelectNode = document.getElementById("SelectNode").value;
	
	var postURL="./ajax_cache_rules.php?mod=rcache&action=list";
	var postStr="node_id="+txtSelectNode;
	
	AjaxBasePost("rcache","list","POST",postURL,postStr);	
	*/
}

function FikCdn_RefreshRCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nNodeID = json["node_id"];
		location.reload();
	}else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];	
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}else{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}
}

function FikCdn_SyncRCacheBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rcache_sync.php?node_id="+txtSelectNode;
	showMSGBOX('',360,165,BT,BL,120,boxURL,'同步页面缓存:');
}

function OnSelectNode(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_rcache.php?node_id="+txtSelectNode;
}
function FikCdn_ToTaskList(){
	parent.window.leftFrame.window.OnSelectNav("span_task_list");
	window.location.href="task_list.php";
}
</script>

<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="cache_rules_fcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt_active">页面缓存</span></a></td>			
				<td height="31" width="85"><a href="cache_rules_rcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt">拒绝缓存</span></a></td>						
				<td height="31" width="85"><a href="cache_rules_rewrite.php?node_id=<?php echo $node_id; ?>"><span class="title_bt_active">转向规则</span></a></td>										
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td>
	  <td valign="top">
	  	  <table width="800" border="0" class="dataintable">
			<tr height="30">
			<td><div class="div_search_title">
									<select id="SelectNode" style="width:280px" onChange="OnSelectNode()">
<?php 	
  	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$node_id 	= mysql_real_escape_string($node_id);
		
		$sql = "SELECT * FROM fikcdn_node;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				$admin_port		= mysql_result($result,$i,"admin_port");
				$password	 	= mysql_result($result,$i,"password");
				$SessionID	 	= mysql_result($result,$i,"SessionID");				
				
				if(strlen($node_id)<=0) $node_id = $this_node_id;
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';
	
				if($this_node_id==$node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
					
					$show_this_name = $sFikIP.' - '.$node_name;	
					
					$this_sFikIP = $sFikIP;
					$this_admin_port = $admin_port;
					$this_password = $password;
					$this_SessionID = $SessionID;		
					$this_group_id = $group_id;		
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
			
			if(strlen($node_id)>0)
			{
				//获取页面缓存规则	
				$aryFikResult = FikApi_RCacheList($this_sFikIP,$this_admin_port,$this_SessionID);
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($this_node_id,$this_sFikIP,$this_admin_port,$this_password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$this_SessionID = $aryRelogin["SessionID"];
							$aryFikResult= FikApi_RCacheList($this_sFikIP,$this_admin_port,$this_SessionID);
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
									VALUES(NULL,'$node_id','$this_group_id','$NO','$Wid','$sUrl','$Icase','$Rules','$Olimit','$CacheLocation','$sNote')";
						$result3 = mysql_query($sql,$db_link);
					}
				}
			}			
		}
	}
?>	
	</select>&nbsp;
	</div></td>
			</tr>
		</table>
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable" id="domain_table">
			<tr id="tr_domain_title">
				<th align="center" width="45">序号</th> 
				<th align="left" width="640">拒绝缓存地址URL</th>
				<th align="center" width="60">大小写</th>
				<th align="center" width="90">拒绝开放权限</th>
				<th align="center" width="90">拒绝缓存位置</th>						
				<th align="center">操作</th>
			</tr>			
<?php
	if($db_link)
	{
		$sql = "SELECT * FROM cache_rule_rcache WHERE node_id='$node_id' ORDER BY NO ASC;";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$row_count = mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");	
				$NO		  		= mysql_result($result,$i,"NO");	
				$Wid	 		= mysql_result($result,$i,"Wid");	
				$Url  			= mysql_result($result,$i,"Url");	
				$Icase   		= mysql_result($result,$i,"Icase");	
				$Rules			= mysql_result($result,$i,"Rules");
				$Olimit			= mysql_result($result,$i,"Olimit");
				$CacheLocation	= mysql_result($result,$i,"CacheLocation");		
				$Note			= mysql_result($result,$i,"Note");	
				

				$Url = urldecode($Url);
				$Note = urldecode($Note);
				
				$sExpire = 	$Expire.' '.$FikFCache_ExpireUnit[$Unit];
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$Note.'" id="tr_fcache_'.$id.'">';
				echo '<td>'.$NO.'</td>';
				echo '<td align="left">'.$Url.'</td>';
				echo '<td>'.$FikFCache_Icase[$Icase].'</td>';
				echo '<td>'.$FikRCache_Olimit[$Olimit].'</td>';
				echo '<td>'.$FikRCache_CacheLocation[$CacheLocation].'</td>';
				echo '<td>  <a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyRCacheBox('.$id.');" title="修改拒绝缓存">修改</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_DelRCacheBox('.$id.');" title="删除拒绝缓存">删除</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_UpRCacheBox('.$id.');" title="向上移动拒绝缓存">上移</a>&nbsp;';
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DownRCacheBox('.$id.');" title="向下移动拒绝缓存">下移</a> </td>';
				echo '</tr>';
			}
		}
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		
		<table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6" height="25">
			</td>
			</tr>
		</table>	
 		<table width="800" border="0" class="bottom_btn">
			<tr>
			<td height="28">
				<input name="btnAddRCacheBox"  type="submit" style="width:100px;height:28px" id="btnAddRCacheBox" value="添加拒绝缓存" style="cursor:pointer;" onClick="FikCdn_AddRCacheBox();" /> 
				<input name="btnSyncRCacheBox"  type="submit" style="width:100px;height:28px" id="btnSyncRCacheBox" value="同步拒绝缓存" style="cursor:pointer;" onClick="FikCdn_SyncRCacheBox();" /> 				
				<input name="btnRefreshRCache"  type="submit" style="width:80px;height:28px" id="btnRefreshRCache" value="刷新" style="cursor:pointer;" onClick="FikCdn_RefreshRCache();" /> 
			</td>
			</tr>
		</table>		
	  </td> 
	  <td background="../images/mail_rightbg.gif">&nbsp;</td>
  </tr>
  
   <tr>
    <td valign="bottom" background="../images/mail_leftbg.gif"><img src="../images/buttom_left2.gif" width="17" height="17" /></td>
    <td background="../images/buttom_bgs.gif"><img src="../images/buttom_bgs.gif" width="17" height="17"></td>
    <td valign="bottom" background="../images/mail_rightbg.gif"><img src="../images/buttom_right2.gif" width="16" height="17" /></td>
  </tr> 
</table>
</div>
<?php

include_once("./tail.php");
?>
