<?php
include_once("./head.php");
include_once('../function/fik_api.php');
$node_id 		= isset($_GET['node_id'])?$_GET['node_id']:'';
?>
<script type="text/javascript">
function FikCdn_AddFCacheBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_fcache_add.php?node_id="+txtSelectNode;
	showMSGBOX('',550,360,BT,BL,120,boxURL,'添加页面缓存:');
}

function FikCdn_ModifyFCacheBox(fid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_fcache_modify.php?node_id="+txtSelectNode+"&fid="+fid;
	showMSGBOX('',550,360,BT,BL,120,boxURL,'修改页面缓存:');
}


var ___nFid;
var ___nNodeId;
function FikCdn_DelFCacheBox(fid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nFid = fid;
	___nNodeId = txtSelectNode;
	var boxURL="msg.php?5.3";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_DelFCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=fcache&action=del";
	var postStr="node_id="+___nNodeId+"&fid="+___nFid;
	
	AjaxBasePost("fcache","del","POST",postURL,postStr);	
}

function FikCdn_DelFCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?5.4";
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

function FikCdn_UpFCacheBox(fid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nFid = fid;
	___nNodeId = txtSelectNode;
	var boxURL="msg.php?5.5";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_UpFCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=fcache&action=up";
	var postStr="node_id="+___nNodeId+"&fid="+___nFid;
	
	AjaxBasePost("fcache","up","POST",postURL,postStr);	
}

function FikCdn_UpFCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?5.7";
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

function FikCdn_DownFCacheBox(fid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nFid = fid;
	___nNodeId = txtSelectNode;
	var boxURL="msg.php?5.6";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_DownFCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=fcache&action=down";
	var postStr="node_id="+___nNodeId+"&fid="+___nFid;
	
	AjaxBasePost("fcache","down","POST",postURL,postStr);	
}

function FikCdn_DownFCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?5.8";
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

function FikCdn_RefreshFCache(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_fcache.php?node_id="+txtSelectNode;
	return;
	/*
	var txtSelectNode = document.getElementById("SelectNode").value;
	
	var postURL="./ajax_cache_rules.php?mod=fcache&action=list";
	var postStr="node_id="+txtSelectNode;
	
	AjaxBasePost("fcache","list","POST",postURL,postStr);	
	*/
}

function FikCdn_RefreshFCacheResult(sResponse){
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

function FikCdn_SyncFCacheBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_fcache_sync.php?node_id="+txtSelectNode;
	showMSGBOX('',360,165,BT,BL,120,boxURL,'同步页面缓存:');
}

function OnSelectNode(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_fcache.php?node_id="+txtSelectNode;
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
				<td height="31" width="85"><a href="cache_rules_fcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt">页面缓存</span></a></td>			
				<td height="31" width="85"><a href="cache_rules_rcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt_active">拒绝缓存</span></a></td>						
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
				$aryFikResult = FikApi_FCacheList($this_sFikIP,$this_admin_port,$this_SessionID);
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($this_node_id,$this_sFikIP,$this_admin_port,$this_password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$this_SessionID = $aryRelogin["SessionID"];
							$aryFikResult= FikApi_FCacheList($this_sFikIP,$this_admin_port,$this_SessionID);
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
									VALUES(NULL,'$node_id','$this_group_id','$NO','$Wid','$sUrl','$Icase','$Rules','$Expire','$Unit','$Icookie','$Olimit','$IsDiskCache','$sNote')";
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
				<th align="left" width="600">缓存地址URL</th> 
				<th align="right" width="60">超时周期</th>
				<th align="center" width="60">大小写</th>
				<th align="center" width="90">开放权限</th>
				<th align="center" width="80" align="center">允许硬盘缓存</th>						
				<th align="center">操作</th>
			</tr>			
<?php
	if($db_link)
	{
		$sql = "SELECT * FROM cache_rule_fcache WHERE node_id='$node_id' ORDER BY NO ASC;";
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
				$Expire	   		= mysql_result($result,$i,"Expire");		
				$Unit			= mysql_result($result,$i,"Unit");
				$Icookie		= mysql_result($result,$i,"Icookie");
				$Olimit			= mysql_result($result,$i,"Olimit");	
				$IsDiskCache	= mysql_result($result,$i,"IsDiskCache");	
				$Note			= mysql_result($result,$i,"Note");	
				
				$Url = urldecode($Url);
				$Note = urldecode($Note);
				
				$sExpire = 	$Expire.' '.$FikFCache_ExpireUnit[$Unit];
				$tips  ="URL匹配规则：".$FikFCache_Rules[$Rules]."\r\n忽略Set-Cookie：".$FikFCache_Icookie[$Icookie]."\r\n备注说明：".$Note;
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$Note.'" id="tr_fcache_'.$id.'">';
				echo '<td>'.$NO.'</td>';
				echo '<td align="left">'.$Url.'</td>';
				echo '<td align="right">'.$sExpire.'</td>';
				echo '<td>'.$FikFCache_Icase[$Icase].'</td>';
				echo '<td>'.$FikFCache_Olimit[$Olimit].'</td>';
				echo '<td>'.$FikFCache_IsDiskCache[$IsDiskCache].'</td>';
				echo '<td>  <a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyFCacheBox('.$id.');" title="修改页面缓存规则">修改</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_DelFCacheBox('.$id.');" title="删除页面缓存规则">删除</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_UpFCacheBox('.$id.');" title="上移一个位置">上移</a>&nbsp;';
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DownFCacheBox('.$id.');" title="下移一个位置">下移</a> </td>';
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
				<input name="btnAddFCacheBox"  type="submit" style="width:100px;height:28px" id="btnAddFCacheBox" value="添加页面缓存" style="cursor:pointer;" onClick="FikCdn_AddFCacheBox();" /> 
				<input name="btnSyncFCacheBox"  type="submit" style="width:100px;height:28px" id="btnSyncFCacheBox" value="同步页面缓存" style="cursor:pointer;" onClick="FikCdn_SyncFCacheBox();" /> 
				<input name="btnRefreshFCache"  type="submit" style="width:80px;height:28px" id="btnRefreshFCache" value="刷新" style="cursor:pointer;" onClick="FikCdn_RefreshFCache();" /> 
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
